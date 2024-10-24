<?php

namespace Mush\Player\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
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
use Mush\Player\Event\PlayerChangedPlaceEvent;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Player\Repository\PlayerInfoRepositoryInterface;
use Mush\Player\Repository\PlayerRepository;
use Mush\RoomLog\Enum\PlayerModifierLogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\User\Entity\User;

final class PlayerService implements PlayerServiceInterface
{
    public const string BASE_PLAYER_CYCLE_CHANGE = 'base_player_cycle_change';
    public const string BASE_PLAYER_DAY_CHANGE = 'base_player_day_change';
    public const int CYCLE_ACTION_CHANGE = 1;
    public const int CYCLE_MOVEMENT_CHANGE = 1;
    public const int CYCLE_SATIETY_CHANGE = -1;
    public const int DAY_HEALTH_CHANGE = 1;
    public const int DAY_MORAL_CHANGE = -2;

    private EntityManagerInterface $entityManager;
    private EventServiceInterface $eventService;
    private PlayerRepository $repository;
    private RoomLogServiceInterface $roomLogService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private RandomServiceInterface $randomService;
    private PlayerInfoRepositoryInterface $playerInfoRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventServiceInterface $eventService,
        PlayerRepository $repository,
        RoomLogServiceInterface $roomLogService,
        GameEquipmentServiceInterface $gameEquipmentService,
        RandomServiceInterface $randomService,
        PlayerInfoRepositoryInterface $playerInfoRepository,
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
        $playerInfo = $this->playerInfoRepository->findOneByUserAndGameStatusOrNull($user, GameStatusEnum::CURRENT);

        return $playerInfo?->getPlayer();
    }

    public function changePlace(Player $player, Place $place): Player
    {
        $oldPlace = $player->getPlace();
        $player->changePlace($place);
        $playerEvent = new PlayerChangedPlaceEvent(
            $player,
            $oldPlace,
        );
        $this->eventService->callEvent($playerEvent, PlayerChangedPlaceEvent::class);

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
                    ->filter(static fn (Place $room) => RoomEnum::LABORATORY === $room->getName())
                    ->first()
            )
            ->setPlayerVariables($characterConfig);

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
            ->setVisibility(VisibilityEnum::PUBLIC);
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
            ->setMessage($message);

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

    /**
     * @psalm-suppress InvalidArgument
     */
    public function handleNewCycle(Player $player, \DateTime $date): Player
    {
        if ($player->isDead()) {
            return $player;
        }

        if ($player->getMoralPoint() === 0) {
            $this->killPlayer(
                player: $player,
                endReason: EndCauseEnum::DEPRESSION,
                time: $date,
            );

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

        if ($player->isActive()) {
            $this->handleTriumphChange($player, $date);
        }

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

    public function killPlayer(Player $player, string $endReason, \DateTime $time = new \DateTime(), ?Player $author = null): Player
    {
        $this->entityManager->beginTransaction();

        try {
            if ($player->isDead()) {
                return $player;
            }

            $this->markPlayerAsDead($player, $endReason, $time);
            $this->removePlayerTitles($player);
            $this->createClosedPlayer($player, $endReason, $time);
            $this->dispatchPlayerDeathEvent($player, $endReason, $time, $author);
            $this->entityManager->commit();
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            $this->entityManager->close();

            throw $e;
        }

        return $player;
    }

    private function handleTriumphChange(Player $player, \DateTime $date): void
    {
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
    }

    private function markPlayerAsDead(Player $player, string $endCause, \DateTime $date): void
    {
        $playerInfo = $player->getPlayerInfo();
        $playerInfo->setGameStatus(GameStatusEnum::FINISHED);
        $this->persistPlayerInfo($playerInfo);
    }

    private function removePlayerTitles(Player $player): void
    {
        $player->removeAllTitles();
        $this->persist($player);
    }

    private function createClosedPlayer(Player $player, string $endCause, \DateTime $date): void
    {
        $closedPlayer = $player->getPlayerInfo()->getClosedPlayer();
        $closedPlayer
            ->setDayCycleDeath($player->getDaedalus())
            ->setEndCause($endCause)
            ->setIsMush($player->isMush())
            ->setClosedDaedalus($player->getDaedalus()->getDaedalusInfo()->getClosedDaedalus())
            ->setFinishedAt($date);
        $this->persistClosedPlayer($closedPlayer);
    }

    private function dispatchPlayerDeathEvent(Player $player, string $endCause, \DateTime $date, ?Player $author = null): void
    {
        $playerDeathEvent = new PlayerEvent($player, [$endCause], $date);
        $playerDeathEvent->setAuthor($author);
        $this->eventService->callEvent($playerDeathEvent, PlayerEvent::DEATH_PLAYER);
    }
}
