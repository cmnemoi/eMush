<?php

namespace Mush\Player\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Enum\TriumphEnum;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Player\Entity\ActionModifier;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Repository\PlayerRepository;
use Mush\Room\Entity\Room;
use Mush\Room\Enum\RoomEnum;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Entity\Status;
use Mush\Status\Service\StatusServiceInterface;
use Mush\User\Entity\User;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PlayerService implements PlayerServiceInterface
{
    private EntityManagerInterface $entityManager;

    private EventDispatcherInterface $eventDispatcher;

    private PlayerRepository $repository;

    private GameConfig $gameConfig;

    private RoomLogServiceInterface $roomLogService;

    private StatusServiceInterface $statusService;

    private TokenStorageInterface $tokenStorage;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        PlayerRepository $repository,
        RoomLogServiceInterface $roomLogService,
        StatusServiceInterface $statusService,
        GameConfigServiceInterface $gameConfigService,
        TokenStorageInterface $tokenStorage
    ) {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->repository = $repository;
        $this->roomLogService = $roomLogService;
        $this->statusService = $statusService;
        $this->gameConfig = $gameConfigService->getConfig();
        $this->tokenStorage = $tokenStorage;
    }

    public function persist(Player $player): Player
    {
        $this->entityManager->persist($player);
        $this->entityManager->flush();

        return $player;
    }

    public function findById(int $id): ?Player
    {
        return $this->repository->find($id);
    }

    public function findOneByCharacter(string $character, ?Daedalus $daedalus = null): ?Player
    {
        return $this->repository->findOneByName($character);
    }

    public function findUserCurrentGame(User $user): ?Player
    {
        return $this->repository->findOneBy(['user' => $user, 'gameStatus' => GameStatusEnum::CURRENT]);
    }

    public function createPlayer(Daedalus $daedalus, string $character): Player
    {
        $player = new Player();

        if (!$token = $this->tokenStorage->getToken()) {
            throw new AccessDeniedException('User should be logged to access that');
        }

        /** @var User $user */
        $user = $token->getUser();

        $characterConfig = $this->gameConfig->getCharactersConfig()->getCharacter($character);
        if (!$characterConfig) {
            throw new \LogicException('Character not available');
        }

        $player
            ->setUser($user)
            ->setGameStatus(GameStatusEnum::CURRENT)
            ->setDaedalus($daedalus)
            ->setRoom(
                $daedalus->getRooms()
                    ->filter(fn (Room $room) => RoomEnum::LABORATORY === $room->getName())
                    ->first()
            )
            ->setCharacterConfig($characterConfig)
            ->setSkills([])
            ->setHealthPoint($this->gameConfig->getInitHealthPoint())
            ->setMoralPoint($this->gameConfig->getInitMoralPoint())
            ->setActionPoint($this->gameConfig->getInitActionPoint())
            ->setMovementPoint($this->gameConfig->getInitMovementPoint())
            ->setSatiety($this->gameConfig->getInitSatiety())
            ->setSatiety($this->gameConfig->getInitSatiety())
        ;

        foreach ($characterConfig->getStatuses() as $statusName) {
            $status = new Status();
            $status
                ->setName($statusName)
                ->setVisibility(VisibilityEnum::PUBLIC)
            ;
            $player->addStatus($status);
        }

        $this->persist($player);

        $user->setCurrentGame($player);
        $playerEvent = new PlayerEvent($player);
        $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::NEW_PLAYER);

        if ($daedalus->getPlayers()->count() === $this->gameConfig->getMaxPlayer()) {
            $fullDaedalusEvent = new DaedalusEvent($daedalus);
            $this->eventDispatcher->dispatch($fullDaedalusEvent, DaedalusEvent::FULL_DAEDALUS);
        }

        return $player;
    }

    public function handleNewCycle(Player $player, \DateTime $date): Player
    {
        if ($player->getMoralPoint() === 0) {
            $playerEvent = new PlayerEvent($player, $date);
            $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::DEATH_PLAYER);

            return $player;
        }

        $actionModifier = new ActionModifier();
        $actionModifier
            ->setActionPointModifier(1)
            ->setMovementPointModifier(1)
            ->setSatietyModifier(-1)
        ;

        $playerEvent = new PlayerEvent($player, $date);
        $playerEvent->setActionModifier($actionModifier);
        $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::MODIFIER_PLAYER);

        $triumphChange = 0;

        if ($player->isMush() &&
            ($mushTriumph = $this->gameConfig->getTriumphConfig()->getTriumph(TriumphEnum::CYCLE_MUSH))
        ) {
            $triumphChange = $mushTriumph->getTriumph();
        }

        if (!$player->isMush() &&
            ($humanTriumph = $this->gameConfig->getTriumphConfig()->getTriumph(TriumphEnum::CYCLE_HUMAN))
        ) {
            $triumphChange = $humanTriumph->getTriumph();
        }

        $player->addTriumph($triumphChange);

        $this->roomLogService->createQuantityLog(
            LogEnum::GAIN_TRIUMPH,
            $player->getRoom(),
            $player,
            VisibilityEnum::PRIVATE,
            $triumphChange,
            $date
        );

        return $this->persist($player);
    }

    public function handleNewDay(Player $player, \DateTime $date): Player
    {
        $actionModifier = new ActionModifier();
        $actionModifier
            ->setHealthPointModifier(1)
            ->setMoralPointModifier(-2) //@TODO check for last hope
        ;

        $playerEvent = new PlayerEvent($player, $date);
        $playerEvent->setActionModifier($actionModifier);
        $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::MODIFIER_PLAYER);

        return $this->persist($player);
    }
}
