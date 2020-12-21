<?php

namespace Mush\Player\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\TriumphEnum;
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
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\PlayerStatusEnum;
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
            ->setPerson($character)
            ->setSkills([])
            ->setHealthPoint($this->gameConfig->getInitHealthPoint())
            ->setMoralPoint($this->gameConfig->getInitMoralPoint())
            ->setActionPoint($this->gameConfig->getInitActionPoint())
            ->setMovementPoint($this->gameConfig->getInitMovementPoint())
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

        if ($player->isMush() &&
            ($mushTriumph = $this->gameConfig->getTriumphConfig()->getTriumph(TriumphEnum::CYCLE_MUSH))
        ) {
            $triumphChange = $mushTriumph->getTriumph();
        }

        $triumphChange = 0;
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

    public function handlePlayerModifier(Player $player, ActionModifier $actionModifier, \DateTime $date = null): Player
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
                $date ?? new \DateTime('now')
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
                $date ?? new \DateTime('now')
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
                $date ?? new \DateTime('now')
            );
        }

        if ($actionModifier->getMoralPointModifier()) {
            if (!$player->isMush()) {
                $playerNewMoralPoint = $player->getMoralPoint() + $actionModifier->getMoralPointModifier();
                $playerNewMoralPoint = $this->getValueInInterval($playerNewMoralPoint, 0, $this->gameConfig->getMaxMoralPoint());
                $player->setMoralPoint($playerNewMoralPoint);

                $demoralizedStatus = $player->getStatusByName(PlayerStatusEnum::DEMORALIZED);
                $suicidalStatus = $player->getStatusByName(PlayerStatusEnum::SUICIDAL);

                if ($player->getMoralPoint() <= 1 && !$suicidalStatus) {
                    $this->statusService->createCorePlayerStatus(PlayerStatusEnum::SUICIDAL, $player);
                } elseif ($suicidalStatus) {
                    $player->removeStatus($suicidalStatus);
                }

                if ($player->getMoralPoint() <= 4 && $player->getMoralPoint() > 1 && $demoralizedStatus) {
                    $this->statusService->createCorePlayerStatus(PlayerStatusEnum::DEMORALIZED, $player);
                } elseif ($demoralizedStatus) {
                    $player->removeStatus($demoralizedStatus);
                }

                $this->roomLogService->createQuantityLog(
                    $actionModifier->getMoralPointModifier() > 0 ? LogEnum::GAIN_MORAL_POINT : LogEnum::LOSS_MORAL_POINT,
                    $player->getRoom(),
                    $player,
                    VisibilityEnum::PRIVATE,
                    $actionModifier->getMoralPointModifier(),
                    $date ?? new \DateTime('now')
                );
            }
        }

        if ($actionModifier->getSatietyModifier()) {
            if ($actionModifier->getSatietyModifier() >= 0 &&
                $player->getSatiety() < 0) {
                $player->setSatiety($actionModifier->getSatietyModifier());
            } else {
                $player->setSatiety($player->getSatiety() + $actionModifier->getSatietyModifier());
            }

            $starvingStatus = $player->getStatusByName(PlayerStatusEnum::STARVING);
            $fullStatus = $player->getStatusByName(PlayerStatusEnum::FULL_STOMACH);

            if (!$player->isMush()) {
                if ($player->getSatiety() < -24 && !$starvingStatus && !$player->isMush()) {
                    $this->statusService->createCorePlayerStatus(PlayerStatusEnum::STARVING, $player);
                } elseif ($starvingStatus) {
                    $player->removeStatus($starvingStatus);
                }

                if ($player->getSatiety() > 3 && !$fullStatus && !$player->isMush()) {
                    $this->statusService->createCorePlayerStatus(PlayerStatusEnum::FULL_STOMACH, $player);
                } elseif ($fullStatus) {
                    $player->removeStatus($fullStatus);
                }
            } elseif ($actionModifier->getSatietyModifier() >= 0) {
                $this->statusService->createChargePlayerStatus(
                    PlayerStatusEnum::FULL_STOMACH,
                    $player,
                    ChargeStrategyTypeEnum::CYCLE_DECREMENT,
                    2,
                    0,
                    true
                );
            }
        }

        return $player;
    }

    private function getValueInInterval(int $value, int $min, int $max): int
    {
        return max(0, min($max, $value));
    }
}
