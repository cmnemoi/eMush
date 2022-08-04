<?php

namespace Mush\Player\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Enum\TriumphEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\DeadPlayerInfo;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Player\Repository\DeadPlayerInfoRepository;
use Mush\Player\Repository\PlayerRepository;
use Mush\RoomLog\Enum\PlayerModifierLogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\User\Entity\User;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PlayerService implements PlayerServiceInterface
{
    public const CYCLE_ACTION_CHANGE = 1;
    public const CYCLE_MOVEMENT_CHANGE = 0;
    public const CYCLE_SATIETY_CHANGE = -1;
    public const DAY_HEALTH_CHANGE = 1;
    public const DAY_MORAL_CHANGE = -2;

    private EntityManagerInterface $entityManager;

    private EventDispatcherInterface $eventDispatcher;

    private PlayerRepository $repository;

    private DeadPlayerInfoRepository $deadPlayerRepository;

    private RoomLogServiceInterface $roomLogService;

    private GameEquipmentServiceInterface $gameEquipmentService;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        PlayerRepository $repository,
        DeadPlayerInfoRepository $deadPlayerRepository,
        RoomLogServiceInterface $roomLogService,
        GameEquipmentServiceInterface $gameEquipmentService
    ) {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->repository = $repository;
        $this->deadPlayerRepository = $deadPlayerRepository;
        $this->roomLogService = $roomLogService;
        $this->gameEquipmentService = $gameEquipmentService;
    }

    public function persist(Player $player): Player
    {
        $this->entityManager->persist($player);
        $this->entityManager->flush();

        return $player;
    }

    public function findById(int $id): ?Player
    {
        $player = $this->repository->find($id);

        return $player instanceof Player ? $player : null;
    }

    public function findOneByCharacter(string $character, Daedalus $daedalus): ?Player
    {
        return $this->repository->findOneByName($character, $daedalus);
    }

    public function findUserCurrentGame(User $user): ?Player
    {
        $player = $this->repository->findOneBy(['user' => $user, 'gameStatus' => GameStatusEnum::CURRENT]);

        return $player instanceof Player ? $player : null;
    }

    public function findDeadPlayerInfo(Player $player): ?DeadPlayerInfo
    {
        return $this->deadPlayerRepository->findOneByPlayer($player);
    }

    public function createPlayer(Daedalus $daedalus, User $user, string $character): Player
    {
        $player = new Player();

        $gameConfig = $daedalus->getGameConfig();

        $characterConfig = $gameConfig->getCharactersConfig()->getCharacter($character);
        if (!$characterConfig) {
            throw new \LogicException('Character not available');
        }

        $player
            ->setUser($user)
            ->setGameStatus(GameStatusEnum::CURRENT)
            ->setDaedalus($daedalus)
            ->setPlace(
                $daedalus->getRooms()
                    ->filter(fn (Place $room) => RoomEnum::LABORATORY === $room->getName())
                    ->first()
            )
            ->setCharacterConfig($characterConfig)
            ->setSkills([])
            ->setHealthPoint($gameConfig->getInitHealthPoint())
            ->setMoralPoint($gameConfig->getInitMoralPoint())
            ->setActionPoint($gameConfig->getInitActionPoint())
            ->setMovementPoint($gameConfig->getInitMovementPoint())
            ->setSatiety($gameConfig->getInitSatiety())
            ->setSatiety($gameConfig->getInitSatiety())
        ;

        $user->setCurrentGame($player);

        $this->persist($player);

        $playerEvent = new PlayerEvent(
            $player,
            EventEnum::CREATE_DAEDALUS,
            new \DateTime()
        );
        $playerEvent
            ->setCharacterConfig($characterConfig)
            ->setVisibility(VisibilityEnum::PUBLIC)
        ;
        $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::NEW_PLAYER);

        return $player;
    }

    public function endPlayer(Player $player, string $message): Player
    {
        $deadPlayerInfo = $this->findDeadPlayerInfo($player);
        if ($deadPlayerInfo === null) {
            throw new \LogicException('unable to find deadPlayerInfo');
        }

        $deadPlayerInfo->setMessage($message);

        $player->setGameStatus(GameStatusEnum::CLOSED);

        $playerEvent = new PlayerEvent(
            $player,
            PlayerEvent::END_PLAYER,
            new \DateTime()
        );
        $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::END_PLAYER);

        $this->entityManager->persist($deadPlayerInfo);
        $this->persist($player);

        return $player;
    }

    public function handleNewCycle(Player $player, \DateTime $date): Player
    {
        if (!$player->isAlive()) {
            return $player;
        }

        if ($player->getMoralPoint() === 0) {
            $playerEvent = new PlayerEvent(
                $player,
                EndCauseEnum::DEPRESSION,
                $date
            );
            $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::DEATH_PLAYER);

            return $player;
        }

        $playerModifierEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::ACTION_POINT,
            self::CYCLE_ACTION_CHANGE,
            EventEnum::NEW_CYCLE,
            $date);
        $this->eventDispatcher->dispatch($playerModifierEvent, AbstractQuantityEvent::CHANGE_VARIABLE);

        $playerModifierEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::MOVEMENT_POINT,
            self::CYCLE_MOVEMENT_CHANGE,
            EventEnum::NEW_CYCLE,
            $date
        );
        $this->eventDispatcher->dispatch($playerModifierEvent, AbstractQuantityEvent::CHANGE_VARIABLE);

        $playerModifierEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::SATIETY,
            self::CYCLE_SATIETY_CHANGE,
            EventEnum::NEW_CYCLE,
            $date
        );
        $this->eventDispatcher->dispatch($playerModifierEvent, AbstractQuantityEvent::CHANGE_VARIABLE);

        $triumphChange = 0;

        $gameConfig = $player->getDaedalus()->getGameConfig();

        if ($player->isMush() &&
            ($mushTriumph = $gameConfig->getTriumphConfig()->getTriumph(TriumphEnum::CYCLE_MUSH))
        ) {
            $triumphChange = $mushTriumph->getTriumph();
        }

        if (!$player->isMush() &&
            ($humanTriumph = $gameConfig->getTriumphConfig()->getTriumph(TriumphEnum::CYCLE_HUMAN))
        ) {
            $triumphChange = $humanTriumph->getTriumph();
        }

        $player->addTriumph($triumphChange);

        $this->roomLogService->createLog(
            PlayerModifierLogEnum::GAIN_TRIUMPH,
            $player->getPlace(),
            VisibilityEnum::PRIVATE,
            'event_log',
            $player,
            ['quantity' => $triumphChange],
            $date
        );

        return $this->persist($player);
    }

    public function handleNewDay(Player $player, \DateTime $date): Player
    {
        if (!$player->isAlive()) {
            return $player;
        }

        $playerModifierEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::HEALTH_POINT,
            self::DAY_HEALTH_CHANGE,
            EventEnum::NEW_DAY,
            $date
        );
        $this->eventDispatcher->dispatch($playerModifierEvent, AbstractQuantityEvent::CHANGE_VARIABLE);

        if (!$player->isMush()) {
            $playerModifierEvent = new PlayerVariableEvent(
                $player,
                PlayerVariableEnum::MORAL_POINT,
                self::DAY_MORAL_CHANGE,
                EventEnum::NEW_DAY,
                $date
            );
            $this->eventDispatcher->dispatch($playerModifierEvent, AbstractQuantityEvent::CHANGE_VARIABLE);
        }

        return $this->persist($player);
    }

    public function playerDeath(Player $player, ?string $reason, \DateTime $time): Player
    {
        if (!$reason) {
            $reason = 'missing end reason';
        }

        $deadPlayerInfo = new DeadPlayerInfo();
        $deadPlayerInfo
            ->setPlayer($player)
            ->setDayDeath($player->getDaedalus()->getDay())
            ->setCycleDeath($player->getDaedalus()->getCycle())
            ->setEndStatus($reason)
        ;

        $this->entityManager->persist($deadPlayerInfo);

        if ($reason !== EndCauseEnum::DEPRESSION) {
            $moraleLoss = -1;
            if ($player->hasStatus(PlayerStatusEnum::PREGNANT)) {
                $moraleLoss = -2;
            }

            /** @var Player $daedalusPlayer */
            foreach ($player->getDaedalus()->getPlayers()->getPlayerAlive() as $daedalusPlayer) {
                if ($daedalusPlayer !== $player) {
                    $playerModifierEvent = new PlayerVariableEvent(
                        $daedalusPlayer,
                        PlayerVariableEnum::MORAL_POINT,
                        $moraleLoss,
                        EventEnum::PLAYER_DEATH,
                        $time
                    );
                    $this->eventDispatcher->dispatch($playerModifierEvent, AbstractQuantityEvent::CHANGE_VARIABLE);
                }
            }
        }

        $currentRoom = $player->getPlace();
        foreach ($player->getEquipments() as $item) {
            $item->setHolder($currentRoom);
            $this->gameEquipmentService->persist($item);
        }

        // @TODO in case of assassination chance of disorder for roommates

        $player->setGameStatus(GameStatusEnum::FINISHED);

        $this->persist($player);

        return $player;
    }
}
