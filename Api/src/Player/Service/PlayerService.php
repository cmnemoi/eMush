<?php

namespace Mush\Player\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Player\Entity\ActionModifier;
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

    public function handleNewCycle(Player $player, \DateTime $time): Player
    {
        if ($player->getMoralPoint() === 0) {
            $playerEvent = new PlayerEvent($player, $time);
            $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::DEATH_PLAYER);
            return $player;
        }

        $actionModifier = new ActionModifier();
        $actionModifier
            ->setActionPointModifier(1)
            ->setMovementPointModifier(1)
        ;

        $playerEvent = new PlayerEvent($player, $time);
        $playerEvent->setActionModifier($actionModifier);
        $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::MODIFIER_PLAYER);

        $player->addTriumph(1);
        $player->addSatiety(-1);

        $this->roomLogService->createQuantityLog(
            LogEnum::GAIN_TRIUMPH,
            $player->getRoom(),
            $player,
            VisibilityEnum::PRIVATE,
            1,
            $time
        );

        return $this->persist($player);
    }

    public function handleNewDay(Player $player, \DateTime $time): Player
    {
        $actionModifier = new ActionModifier();
        $actionModifier
            ->setHealthPointModifier(1)
            ->setMoralPointModifier(-2) //@TODO check for last hope
        ;

        $playerEvent = new PlayerEvent($player, $time);
        $playerEvent->setActionModifier($actionModifier);
        $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::MODIFIER_PLAYER);

        return $this->persist($player);
    }

    public function handlePlayerModifier(Player $player, ActionModifier $actionModifier, \DateTime $time = null): Player
    {
        if ($actionModifier->getActionPointModifier() !== 0) {
            $playerNewActionPoint = $player->getActionPoint() + $actionModifier->getActionPointModifier();
            $playerNewActionPoint = $this->getValueInInterval($playerNewActionPoint, 0, $this->gameConfig->getMaxActionPoint());
            $player->setActionPoint($playerNewActionPoint);
            $this->roomLogService->createQuantityLog(
                $actionModifier->getActionPointModifier() > 0 ? LogEnum::GAIN_ACTION_POINT : LogEnum::LOSS_ACTION_POINT,
                $player->getRoom(),
                $player,
                VisibilityEnum::PRIVATE,
                $actionModifier->getActionPointModifier(),
                $time ?? new \DateTime('now')
            );
        }

        if ($actionModifier->getMovementPointModifier()) {
            $playerNewMovementPoint = $player->getMovementPoint() + $actionModifier->getMovementPointModifier();
            $playerNewMovementPoint = $this->getValueInInterval($playerNewMovementPoint, 0, $this->gameConfig->getMaxMovementPoint());
            $player->setMovementPoint($playerNewMovementPoint);
            $this->roomLogService->createQuantityLog(
                $actionModifier->getMovementPointModifier() > 0 ? LogEnum::GAIN_MOVEMENT_POINT : LogEnum::LOSS_MOVEMENT_POINT,
                $player->getRoom(),
                $player,
                VisibilityEnum::PRIVATE,
                $actionModifier->getMovementPointModifier(),
                $time ?? new \DateTime('now')
            );
        }

        if ($actionModifier->getHealthPointModifier()) {
            $playerNewHealthPoint = $player->getHealthPoint() + $actionModifier->getHealthPointModifier();
            $playerNewHealthPoint = $this->getValueInInterval($playerNewHealthPoint, 0, $this->gameConfig->getMaxHealthPoint());
            $player->setHealthPoint($playerNewHealthPoint);
            $this->roomLogService->createQuantityLog(
                $actionModifier->getHealthPointModifier() > 0 ? LogEnum::GAIN_HEALTH_POINT : LogEnum::LOSS_HEALTH_POINT,
                $player->getRoom(),
                $player,
                VisibilityEnum::PRIVATE,
                $actionModifier->getHealthPointModifier(),
                $time ?? new \DateTime('now')
            );
        }

        if ($actionModifier->getMoralPointModifier()) {
            $playerNewMoralPoint = $player->getMoralPoint() + $actionModifier->getMoralPointModifier();
            $playerNewMoralPoint = $this->getValueInInterval($playerNewMoralPoint, 0, $this->gameConfig->getMaxMoralPoint());
            $player->setMoralPoint($playerNewMoralPoint);
            $this->roomLogService->createQuantityLog(
                $actionModifier->getMoralPointModifier() > 0 ? LogEnum::GAIN_MORAL_POINT : LogEnum::LOSS_MORAL_POINT,
                $player->getRoom(),
                $player,
                VisibilityEnum::PRIVATE,
                $actionModifier->getMoralPointModifier(),
                $time ?? new \DateTime('now')
            );
        }

        return $player;
    }

    private function getValueInInterval(int $value, int $min, int $max) : int
    {
        return max(0, min($max, $value));
    }
}
