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
use Mush\Player\Entity\ClosedPlayer;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
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
        GameEquipmentServiceInterface $gameEquipmentService,
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

    public function persistPlayerInfo(PlayerInfo $player): PlayerInfo
    {
        $this->entityManager->persist($player);
        $this->entityManager->flush();

        return $player;
    }

    public function persistClosedPlayer(ClosedPlayer $player): ClosedPlayer
    {
        $this->entityManager->persist($player);
        $this->entityManager->flush();

        return $player;
    }

    public function delete(Player $player): bool
    {
        $playerInfo = $player->getPlayerInfo();
        $playerInfo->deletePlayer();
        $this->persistPlayerInfo($playerInfo);

        $daedalus = $player->getDaedalus();
        $daedalus->removePlayer($player);
        $this->entityManager->persist($daedalus);

        $this->entityManager->remove($player);
        $this->entityManager->flush();

        return true;
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
        $playerInfo = $this->repository->findOneBy(['user' => $user, 'gameStatus' => GameStatusEnum::CURRENT]);

        return $playerInfo instanceof PlayerInfo ? $playerInfo->getPlayer() : null;
    }

    public function createPlayer(Daedalus $daedalus, User $user, string $character): Player
    {
        $player = new Player();
        $time = new \DateTime();

        $gameConfig = $daedalus->getGameConfig();

        $characterConfig = $gameConfig->getCharactersConfig()->getCharacter($character);
        if (!$characterConfig) {
            throw new \LogicException('Character not available');
        }

        $player
            ->setDaedalus($daedalus)
            ->setPlace(
                $daedalus->getRooms()
                    ->filter(fn (Place $room) => RoomEnum::LABORATORY === $room->getName())
                    ->first()
            )
            ->setSkills([])
            ->setHealthPoint($characterConfig->getInitHealthPoint())
            ->setMoralPoint($characterConfig->getInitMoralPoint())
            ->setActionPoint($characterConfig->getInitActionPoint())
            ->setMovementPoint($characterConfig->getInitMovementPoint())
            ->setSatiety($characterConfig->getInitSatiety())
        ;

        $playerInfo = new PlayerInfo(
            $player,
            $user,
            $characterConfig
        );

        $this->persistPlayerInfo($playerInfo);

        $user->startGame();
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $playerEvent = new PlayerEvent(
            $player,
            EventEnum::CREATE_DAEDALUS,
            $time
        );
        $playerEvent
            ->setCharacterConfig($characterConfig)
            ->setVisibility(VisibilityEnum::PUBLIC)
        ;
        $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::NEW_PLAYER);

        foreach ($characterConfig->getStartingItem() as $itemConfig) {
            // Create the equipment
            $item = $this->gameEquipmentService->createGameEquipment(
                $itemConfig,
                $player,
                PlayerEvent::NEW_PLAYER,
                VisibilityEnum::PRIVATE
            );
        }

        return $player;
    }

    public function endPlayer(Player $player, string $message): Player
    {
        $playerInfo = $player->getPlayerInfo();

        /** @var ClosedPlayer $closedPlayer */
        $closedPlayer = $playerInfo->getClosedPlayer();

        $closedPlayer
            ->setMessage($message)
        ;

        $playerInfo->setGameStatus(GameStatusEnum::CLOSED);
        $this->persistPlayerInfo($playerInfo);

        $playerEvent = new PlayerEvent(
            $player,
            PlayerEvent::END_PLAYER,
            new \DateTime()
        );
        $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::END_PLAYER);

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

        $currentRoom = $player->getPlace();
        foreach ($player->getEquipments() as $item) {
            $item->setHolder($currentRoom);
            $this->gameEquipmentService->persist($item);
        }

        $playerInfo = $player->getPlayerInfo();
        $closedPlayer = $playerInfo->getClosedPlayer();
        $playerInfo->setGameStatus(GameStatusEnum::FINISHED);
        $closedPlayer
            ->setDayCycleDeath($player->getDaedalus())
            ->setEndCause($reason)
            ->setIsMush($player->isMush())
        ;
        $this->persistPlayerInfo($playerInfo);

        if ($reason !== EndCauseEnum::DEPRESSION) {
            $moraleLoss = -1;
            if ($player->hasStatus(PlayerStatusEnum::PREGNANT)) {
                $moraleLoss = -2;
            }

            /** @var Player $daedalusPlayer */
            foreach ($player->getDaedalus()->getPlayers()->getPlayerAlive() as $daedalusPlayer) {
                if ($daedalusPlayer !== $player && !$daedalusPlayer->isMush()) {
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

        // @TODO in case of assassination chance of disorder for roommates
        return $player;
    }
}
