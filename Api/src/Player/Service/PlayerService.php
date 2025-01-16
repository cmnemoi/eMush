<?php

namespace Mush\Player\Service;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Enum\TriumphEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Modifier\Enum\ModifierNameEnum;
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
use Mush\Skill\Enum\SkillEnum;
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
    public const int SELF_SACRIFICE_HEALTH_LOSS = -1;

    private EntityManagerInterface $entityManager;
    private EventServiceInterface $eventService;
    private PlayerRepository $repository;
    private RoomLogServiceInterface $roomLogService;
    private PlayerInfoRepositoryInterface $playerInfoRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventServiceInterface $eventService,
        PlayerRepository $repository,
        RoomLogServiceInterface $roomLogService,
        PlayerInfoRepositoryInterface $playerInfoRepository,
    ) {
        $this->entityManager = $entityManager;
        $this->eventService = $eventService;
        $this->repository = $repository;
        $this->roomLogService = $roomLogService;
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

        $this->eventService->callEvent(
            event: new PlayerEvent(player: $player, tags: [], time: new \DateTime()),
            name: PlayerEvent::DELETE_PLAYER
        );

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
        $this->entityManager->beginTransaction();

        try {
            $time = new \DateTime();

            $player = $this->buildPlayer($daedalus, $user, $character);
            $this->dispatchNewPlayerEvent($player, $time);

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            $this->entityManager->close();

            throw $e;
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
                && $likedPlayer->notEquals($player)
                && $likedPlayer->getDaedalus()->equals($player->getDaedalus())
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

        if ($player->hasZeroMoralPoint()) {
           $this->handleZeroMoralPointEffects($player, $date);
           //misleadingly "duplicate" verification necessary to handle self-sacrifice
           if ($player->isDead()) {
            return $player;
            }
        }

        $this->applyCycleChangesPointsGain($player, $date);

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
            $this->entityManager->lock($player, LockMode::PESSIMISTIC_WRITE);
            $this->entityManager->refresh($player);

            if ($player->isDead()) {
                return $player;
            }

            $this->markPlayerAsDead($player, $endReason, $time);
            $this->removePlayerTitles($player);
            $this->createClosedPlayer($player, $endReason, $time);
            $this->dispatchPlayerDeathEvent($player, $endReason, $time, $author);
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            $this->entityManager->close();

            throw $e;
        }

        return $player;
    }

    private function handleZeroMoralPointEffects(Player $player, \DateTime $date): Player
    {
        if ($player->hasSkill(SkillEnum::SELF_SACRIFICE)) {
            $this->applySelfSacrifice($player, $date);
        } else {
            $this->killPlayer(
                player: $player,
                endReason: EndCauseEnum::DEPRESSION,
                time: $date,
            );
        }

        return $player;
    }

    private function applySelfSacrifice(Player $player, \DateTime $date): void
    {
        $playerVariableEvent = new PlayerVariableEvent(
            player: $player,
            variableName: PlayerVariableEnum::HEALTH_POINT,
            quantity: self::SELF_SACRIFICE_HEALTH_LOSS,
            tags: [EventEnum::NEW_CYCLE, ModifierNameEnum::SELF_SACRIFICE_MODIFIER],
            time: $date
        );
        $this->eventService->callEvent($playerVariableEvent, VariableEventInterface::CHANGE_VARIABLE);
    }

    private function applyCycleChangesPointsGain(Player $player, \DateTime $date): void
    {
        $changes = [
            PlayerVariableEnum::ACTION_POINT => self::CYCLE_ACTION_CHANGE,
            PlayerVariableEnum::MOVEMENT_POINT => self::CYCLE_MOVEMENT_CHANGE,
            PlayerVariableEnum::SATIETY => self::CYCLE_SATIETY_CHANGE,
        ];

        foreach ($changes as $variableName => $quantity) {
            $playerVariableEvent = new PlayerVariableEvent(
                $player,
                $variableName,
                $quantity,
                [EventEnum::NEW_CYCLE, self::BASE_PLAYER_CYCLE_CHANGE],
                $date
            );
            $this->eventService->callEvent($playerVariableEvent, VariableEventInterface::CHANGE_VARIABLE);
        }
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

    private function buildPlayer(Daedalus $daedalus, User $user, string $character): Player
    {
        $characterConfig = $daedalus->getGameConfig()->getCharactersConfig()->getByNameOrThrow($character);

        $laboratory = $daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY);
        $player = new Player();
        $player
            ->setDaedalus($daedalus)
            ->setPlace($laboratory)
            ->setPlayerVariables($characterConfig);

        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $this->persistPlayerInfo($playerInfo);
        $this->persist($player);

        return $player;
    }

    private function dispatchNewPlayerEvent(Player $player, \DateTime $time): void
    {
        $playerEvent = new PlayerEvent(
            player: $player,
            tags: [EventEnum::CREATE_DAEDALUS],
            time: $time
        );
        $playerEvent
            ->setCharacterConfig($player->getCharacterConfig())
            ->setVisibility(VisibilityEnum::PUBLIC);

        $this->eventService->callEvent($playerEvent, PlayerEvent::NEW_PLAYER);
    }
}
