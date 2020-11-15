<?php

namespace Mush\Player\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\GameStatusEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Repository\PlayerRepository;
use Mush\Room\Entity\Room;
use Mush\Room\Enum\RoomEnum;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Entity\Status;
use Mush\User\Entity\User;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PlayerService implements PlayerServiceInterface
{
    private EntityManagerInterface $entityManager;

    private EventDispatcherInterface $eventDispatcher;

    private PlayerRepository $repository;

    private GameConfig $gameConfig;

    private RoomLogServiceInterface $roomLogService;

    private TokenStorageInterface $tokenStorage;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        PlayerRepository $repository,
        RoomLogServiceInterface $roomLogService,
        GameConfigServiceInterface $gameConfigService,
        TokenStorageInterface $tokenStorage
    ) {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->repository = $repository;
        $this->roomLogService = $roomLogService;
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
        $params = ['person' => $character];

        if (null !== $daedalus) {
            $params['daedalus'] = $daedalus;
        }

        return $this->repository->findOneBy($params);
    }

    public function findUserCurrentGame(User $user): ?Player
    {
        return $this->repository->findOneBy(['user' => $user, 'gameStatus' => GameStatusEnum::CURRENT]);
    }

    public function createPlayer(Daedalus $daedalus, string $character): Player
    {
        $player = new Player();

        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();

        $characterConfig = $this->gameConfig->getCharactersConfig()->getCharacter($character);

        $statuses = new ArrayCollection();
        foreach ($characterConfig->getStatuses() as $statusName) {
            $status = new Status();
            $status
                ->setName($statusName)
                ->setVisibility(VisibilityEnum::PUBLIC)
            ;
            $statuses->add($status);
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
            ->setPerson($character)
            ->setSkills([])
            ->setHealthPoint($this->gameConfig->getInitHealthPoint())
            ->setMoralPoint($this->gameConfig->getInitMoralPoint())
            ->setActionPoint($this->gameConfig->getInitActionPoint())
            ->setMovementPoint($this->gameConfig->getInitMovementPoint())
            ->setSatiety($this->gameConfig->getInitSatiety())
            ->setStatuses($statuses)
        ;

        $user->setCurrentGame($player);
        $playerEvent = new PlayerEvent($player);
        $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::NEW_PLAYER);

        if ($daedalus->getPlayers()->count() === $this->gameConfig->getMaxPlayer()) {
            $fullDaedalusEvent = new DaedalusEvent($daedalus);
            $this->eventDispatcher->dispatch($fullDaedalusEvent, DaedalusEvent::FULL_DAEDALUS);
        }

        return $this->persist($player);
    }

    public function handleNewCycle(Player $player, \DateTime $date): Player
    {
        $player->addActionPoint(1);
        $player->addMovementPoint(1);
        $player->addTriumph(1);
        $player->addSatiety(-1);

        $this->roomLogService->createQuantityLog(
            LogEnum::GAIN_ACTION_POINT,
            $player->getRoom(),
            $player,
            VisibilityEnum::PRIVATE,
            1,
            $date
        );
        $this->roomLogService->createQuantityLog(
            LogEnum::GAIN_MOVEMENT_POINT,
            $player->getRoom(),
            $player,
            VisibilityEnum::PRIVATE,
            1,
            $date
        );
        $this->roomLogService->createQuantityLog(
            LogEnum::GAIN_TRIUMPH,
            $player->getRoom(),
            $player,
            VisibilityEnum::PRIVATE,
            1,
            $date
        );

        return $this->persist($player);
    }

    public function handleNewDay(Player $player, \DateTime $date): Player
    {
        $player->addHealthPoint(1);
        $player->addMoralPoint(-2);

        $this->roomLogService->createQuantityLog(
            LogEnum::GAIN_HEALTH_POINT,
            $player->getRoom(),
            $player,
            VisibilityEnum::PRIVATE,
            1,
            $date
        );
        $this->roomLogService->createQuantityLog(
            LogEnum::LOSS_MORAL_POINT,
            $player->getRoom(),
            $player,
            VisibilityEnum::PRIVATE,
            2,
            $date
        );

        return $this->persist($player);
    }
}
