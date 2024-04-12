<?php

namespace Mush\Player\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Enum\TriumphEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\ClosedPlayer;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Player\Repository\PlayerInfoRepository;
use Mush\Player\Repository\PlayerRepository;
use Mush\RoomLog\Enum\PlayerModifierLogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\User\Entity\User;

class PlayerService implements PlayerServiceInterface
{
    public const BASE_PLAYER_CYCLE_CHANGE = 'base_player_cycle_change';
    public const BASE_PLAYER_DAY_CHANGE = 'base_player_day_change';
    public const CYCLE_ACTION_CHANGE = 1;
    public const CYCLE_MOVEMENT_CHANGE = 1;
    public const CYCLE_SATIETY_CHANGE = -1;
    public const DAY_HEALTH_CHANGE = 1;
    public const DAY_MORAL_CHANGE = -2;
    public const NB_ORGANIC_WASTE_MIN = 3;
    public const NB_ORGANIC_WASTE_MAX = 4;

    private EntityManagerInterface $entityManager;

    private EventServiceInterface $eventService;

    private PlayerRepository $repository;

    private RoomLogServiceInterface $roomLogService;

    private GameEquipmentServiceInterface $gameEquipmentService;

    private RandomServiceInterface $randomService;

    private PlayerInfoRepository $playerInfoRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventServiceInterface $eventService,
        PlayerRepository $repository,
        RoomLogServiceInterface $roomLogService,
        GameEquipmentServiceInterface $gameEquipmentService,
        RandomServiceInterface $randomService,
        PlayerInfoRepository $playerInfoRepository
    ) {
        $this->entityManager = $entityManager;
        $this->eventService = $eventService;
        $this->repository = $repository;
        $this->roomLogService = $roomLogService;
        $this->gameEquipmentService = $gameEquipmentService;
        $this->randomService = $randomService;
        $this->playerInfoRepository = $playerInfoRepository;
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

    public function findAll(): array
    {
        return $this->repository->findAll();
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
        $playerInfo = $this->playerInfoRepository->findOneBy(['user' => $user, 'gameStatus' => GameStatusEnum::CURRENT]);

        return $playerInfo instanceof PlayerInfo ? $playerInfo->getPlayer() : null;
    }

    public function changePlace(Player $player, Place $place): Player
    {
        $player->changePlace($place);
        $playerEvent = new PlayerEvent(
            $player,
            [PlayerEvent::CHANGED_PLACE],
            new \DateTime()
        );
        $this->eventService->callEvent($playerEvent, PlayerEvent::CHANGED_PLACE);

        return $this->persist($player);
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
            ->setPlayerVariables($characterConfig)
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
            [EventEnum::CREATE_DAEDALUS],
            $time
        );
        $playerEvent
            ->setCharacterConfig($characterConfig)
            ->setVisibility(VisibilityEnum::PUBLIC)
        ;
        $this->eventService->callEvent($playerEvent, PlayerEvent::NEW_PLAYER);

        foreach ($characterConfig->getStartingItems() as $itemConfig) {
            // Create the equipment
            $item = $this->gameEquipmentService->createGameEquipment(
                $itemConfig,
                $player,
                [PlayerEvent::NEW_PLAYER],
                $time,
                VisibilityEnum::PRIVATE
            );
        }

        return $player;
    }

    public function endPlayer(Player $player, string $message, array $likedPlayers = []): Player
    {
        $playerInfo = $player->getPlayerInfo();

        /** @var ClosedPlayer $closedPlayer */
        $closedPlayer = $playerInfo->getClosedPlayer();

        $closedPlayer
            ->setMessage($message)
        ;

        // Avoid duplicates
        $likedPlayers = array_unique($likedPlayers);

        foreach ($likedPlayers as $likedPlayerId) {
            $likedPlayer = $this->findById($likedPlayerId);

            // Only keep players that are not source player and that are in same daedalus
            if ($likedPlayer
                && $likedPlayer->getId() !== $player->getId()
                && $likedPlayer->getDaedalus()->getId() === $player->getDaedalus()->getId()
            ) {
                $likedClosedPlayer = $likedPlayer->getPlayerInfo()->getClosedPlayer();
                $likedClosedPlayer->addLike();
                $this->persistClosedPlayer($likedClosedPlayer);
            }
        }

        $playerInfo->setGameStatus(GameStatusEnum::CLOSED);
        $this->persistPlayerInfo($playerInfo);

        $playerEvent = new PlayerEvent(
            $player,
            [PlayerEvent::END_PLAYER],
            new \DateTime()
        );
        $this->eventService->callEvent($playerEvent, PlayerEvent::END_PLAYER);

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
                [EndCauseEnum::DEPRESSION],
                $date
            );
            $this->eventService->callEvent($playerEvent, PlayerEvent::DEATH_PLAYER);

            return $player;
        }

        $playerVariableEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::ACTION_POINT,
            self::CYCLE_ACTION_CHANGE,
            [EventEnum::NEW_CYCLE, self::BASE_PLAYER_CYCLE_CHANGE],
            $date
        );
        $this->eventService->callEvent($playerVariableEvent, VariableEventInterface::CHANGE_VARIABLE);

        $playerVariableEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::MOVEMENT_POINT,
            self::CYCLE_MOVEMENT_CHANGE,
            [EventEnum::NEW_CYCLE, self::BASE_PLAYER_CYCLE_CHANGE],
            $date
        );
        $this->eventService->callEvent($playerVariableEvent, VariableEventInterface::CHANGE_VARIABLE);

        $playerVariableEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::SATIETY,
            self::CYCLE_SATIETY_CHANGE,
            [EventEnum::NEW_CYCLE, self::BASE_PLAYER_CYCLE_CHANGE],
            $date
        );
        $this->eventService->callEvent($playerVariableEvent, VariableEventInterface::CHANGE_VARIABLE);

        $triumphChange = 0;
        $gameConfig = $player->getDaedalus()->getGameConfig();

        if ($player->isMush() && ($mushTriumph = $gameConfig->getTriumphConfig()->getTriumph(TriumphEnum::CYCLE_MUSH))) {
            $triumphChange = $mushTriumph->getTriumph();
        }
        if (!$player->isMush() && ($humanTriumph = $gameConfig->getTriumphConfig()->getTriumph(TriumphEnum::CYCLE_HUMAN))) {
            $triumphChange = $humanTriumph->getTriumph();
        }

        $player->addTriumph($triumphChange);

        if ($player->getTriumph() < 0) {
            $player->setTriumph(0);
        }

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
            [EventEnum::NEW_DAY, self::BASE_PLAYER_DAY_CHANGE],
            $date
        );
        $this->eventService->callEvent($playerModifierEvent, VariableEventInterface::CHANGE_VARIABLE);

        $playerModifierEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::MORAL_POINT,
            self::DAY_MORAL_CHANGE,
            [EventEnum::NEW_DAY, self::BASE_PLAYER_DAY_CHANGE, self::DAY_MORAL_CHANGE],
            $date
        );
        $this->eventService->callEvent($playerModifierEvent, VariableEventInterface::CHANGE_VARIABLE);

        return $this->persist($player);
    }

    public function playerDeath(Player $player, string $endReason, \DateTime $time): Player
    {
        $currentRoom = $player->getPlace();
        foreach ($player->getEquipments() as $item) {
            $item->setHolder($currentRoom);
            $this->gameEquipmentService->persist($item);
        }

        if ($endReason === EndCauseEnum::QUARANTINE) {
            $this->handleQuarantineCompensation($player->getPlace());
        }

        $playerInfo = $player->getPlayerInfo();
        $closedPlayer = $playerInfo->getClosedPlayer();
        $playerInfo->setGameStatus(GameStatusEnum::FINISHED);
        $closedPlayer
            ->setDayCycleDeath($player->getDaedalus())
            ->setEndCause($endReason)
            ->setIsMush($player->isMush())
            ->setClosedDaedalus($player->getDaedalus()->getDaedalusInfo()->getClosedDaedalus())
            ->setFinishedAt($time)
        ;
        $this->persistPlayerInfo($playerInfo);

        if (!in_array($endReason, [EndCauseEnum::DEPRESSION, EndCauseEnum::QUARANTINE])) {
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
                        [EventEnum::PLAYER_DEATH],
                        $time
                    );
                    $this->eventService->callEvent($playerModifierEvent, VariableEventInterface::CHANGE_VARIABLE);
                }
            }
        }

        return $player;
    }

    /**
     * This function handle the compensation for a player who died because of quarantine.
     * Currently it drops 3-4 organic waste.
     * TODO: add more powerful compensation?
     */
    private function handleQuarantineCompensation(Place $playerDeathPlace): void
    {
        $nbOrganicWaste = $this->randomService->random(self::NB_ORGANIC_WASTE_MIN, self::NB_ORGANIC_WASTE_MAX);

        for ($i = 0; $i < $nbOrganicWaste; ++$i) {
            $this->gameEquipmentService->createGameEquipmentFromName(
                GameRationEnum::ORGANIC_WASTE,
                $playerDeathPlace,
                [EndCauseEnum::QUARANTINE],
                new \DateTime()
            );
        }
    }
}
