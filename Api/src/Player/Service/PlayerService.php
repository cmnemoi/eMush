<?php

namespace Mush\Player\Service;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\DBAL\LockMode;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Repository\DaedalusRepositoryInterface;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\GameStatusEnum;
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
use Mush\Player\Repository\ClosedPlayerRepositoryInterface;
use Mush\Player\Repository\PlayerInfoRepositoryInterface;
use Mush\Player\Repository\PlayerRepositoryInterface;
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

    public function __construct(
        private ClosedPlayerRepositoryInterface $closedPlayerRepository,
        private DaedalusRepositoryInterface $daedalusRepository,
        private EventServiceInterface $eventService,
        private PlayerRepositoryInterface $playerRepository,
        private RoomLogServiceInterface $roomLogService,
        private PlayerInfoRepositoryInterface $playerInfoRepository,
        private bool $antiSpam,
    ) {}

    public function persist(Player $player): Player
    {
        $this->playerRepository->save($player);

        return $player;
    }

    public function persistPlayerInfo(PlayerInfo $player): PlayerInfo
    {
        $this->playerInfoRepository->save($player);

        return $player;
    }

    public function persistClosedPlayer(ClosedPlayer $player): ClosedPlayer
    {
        $this->closedPlayerRepository->save($player);

        return $player;
    }

    public function delete(Player $player): bool
    {
        $playerInfo = $player->getPlayerInfo();
        $playerInfo->deletePlayer();
        $this->persistPlayerInfo($playerInfo);

        $daedalus = $player->getDaedalus();
        $daedalus->removePlayer($player);
        $this->daedalusRepository->save($daedalus);

        $this->eventService->callEvent(
            event: new PlayerEvent(player: $player, tags: [], time: new \DateTime()),
            name: PlayerEvent::DELETE_PLAYER
        );

        $this->playerRepository->delete($player);

        return true;
    }

    public function findAll(): array
    {
        return $this->playerRepository->getAll();
    }

    public function findById(int $id): ?Player
    {
        return $this->playerRepository->findById($id);
    }

    public function findOneByCharacter(string $character, Daedalus $daedalus): ?Player
    {
        return $this->playerRepository->findOneByNameAndDaedalus($character, $daedalus);
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
        try {
            $this->playerRepository->startTransaction();
            if ($this->antiSpam && $user->getCreatedAt() > new \DateTime('24 hours ago')) {
                throw new \Exception('User too recent to play');
            }

            // Lock Daedalus to prevent concurrent player creation
            $daedalus = $this->daedalusRepository->lockAndRefresh($daedalus);

            // Check if daedalus is not filled
            if (!$daedalus->isFilling()) {
                throw new \RuntimeException('Cannot join a Daedalus already filled');
            }

            // Check if player already exists
            $existingPlayer = $this->playerRepository->findOneByUserAndDaedalus($user, $daedalus);
            if ($existingPlayer) {
                $this->playerRepository->commitTransaction();

                return $existingPlayer;
            }

            $player = $this->buildPlayer($daedalus, $user, $character);
            $this->saveSafely($player);
        } catch (\Throwable $e) {
            $this->playerRepository->rollbackTransaction();

            throw $e;
        }

        return $player;
    }

    public function endPlayer(Player $player, string $message, array $likedPlayers = []): Player
    {
        $playerInfo = $player->getPlayerInfo();

        $closedPlayer = $playerInfo->getClosedPlayer();
        $closedPlayer->setMessage($message);

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
                $this->eventService->callEvent(new PlayerEvent($likedPlayer, [], new \DateTime()), PlayerEvent::PLAYER_GOT_LIKED);
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
            // misleadingly "duplicate" verification necessary to handle self-sacrifice
            if ($player->isDead()) {
                return $player;
            }
        }

        $this->applyCycleChangesPointsGain($player, $date);

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

    public function killPlayer(Player $player, string $endReason, \DateTime $time = new \DateTime(), array $tags = [], ?Player $author = null): Player
    {
        try {
            $this->playerRepository->startTransaction();
            $this->playerRepository->lockAndRefresh($player, LockMode::PESSIMISTIC_WRITE);

            if ($player->isDead()) {
                $this->playerRepository->save($player);
                $this->playerRepository->commitTransaction();

                return $player;
            }

            $this->removePlayerTitles($player);
            $this->createClosedPlayer($player, $endReason, $time);
            $this->markPlayerAsDead($player, $endReason, $time);
            $this->dispatchPlayerDeathEvent($player, $endReason, $tags, $time, $author);
            $this->playerRepository->save($player);
            $this->playerRepository->commitTransaction();
        } catch (\Throwable $e) {
            $this->playerRepository->rollbackTransaction();

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

    private function dispatchPlayerDeathEvent(Player $player, string $endCause, array $tags, \DateTime $date, ?Player $author = null): void
    {
        $playerDeathEvent = new PlayerEvent($player, $tags, $date);
        $playerDeathEvent
            ->setAuthor($author)
            ->addTag($endCause)
            ->addTag($player->getName());

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
            ->setPlayerVariables($characterConfig)
            ->setAvailableHumanSkills($characterConfig->getSkillConfigs());

        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $this->persistPlayerInfo($playerInfo);
        $this->persist($player);

        return $player;
    }

    private function saveSafely(Player $player): void
    {
        try {
            $this->playerRepository->save($player);
            $this->dispatchNewPlayerEvent($player, new \DateTime());
            $this->playerRepository->commitTransaction();
        } catch (UniqueConstraintViolationException $e) {
            throw new \RuntimeException('Unique constraint violation occurred but could not find existing player', 0, $e);
        }
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

        if ($player->getDaedalus()->getPlayers()->count() === 1) {
            $playerEvent->addTag(PlayerEvent::FIRST_PLAYER_ON_BOARD);
        }

        $this->eventService->callEvent($playerEvent, PlayerEvent::NEW_PLAYER);
    }
}
