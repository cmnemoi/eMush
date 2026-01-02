<?php

declare(strict_types=1);

namespace Mush\Achievement\Listener;

use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Services\UpdatePlayerStatisticService;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Game\Enum\TitleEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class PlayerEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private UpdatePlayerStatisticService $updatePlayerStatisticService) {}

    public static function getSubscribedEvents(): array
    {
        return [
            PlayerEvent::CONVERSION_PLAYER => ['onConversionPlayer', EventPriorityEnum::LOWEST],
            PlayerEvent::DEATH_PLAYER => ['onPlayerDeath', EventPriorityEnum::LOWEST],
            PlayerEvent::END_PLAYER => ['onPlayerEnd', EventPriorityEnum::LOWEST],
            PlayerEvent::PLAYER_GOT_LIKED => ['onPlayerGotLiked', EventPriorityEnum::LOWEST],
            PlayerEvent::TITLE_REMOVED => ['onTitleRemoved', EventPriorityEnum::LOWEST],
        ];
    }

    public function onConversionPlayer(PlayerEvent $event): void
    {
        if ($event->hasTag(ActionEnum::EXCHANGE_BODY->toString())) {
            return;
        }

        // Increment for the converted player (mushed)
        $this->updatePlayerStatisticService->execute(
            player: $event->getPlayer(),
            statisticName: StatisticEnum::MUSHED,
        );

        // Increment for the author (has_mushed) if there is an author
        $author = $event->getAuthor();
        if ($author !== null) {
            $this->updatePlayerStatisticService->execute(
                player: $author,
                statisticName: StatisticEnum::HAS_MUSHED,
            );
        }
    }

    public function onPlayerEnd(PlayerEvent $event): void
    {
        $player = $event->getPlayer();
        $closedPlayer = $player->getPlayerInfo()->getClosedPlayer();
        $nbSleptCycles = $player->getPlayerInfo()->getStatistics()->getSleptCycles();

        if ($closedPlayer->getDayDeath() < 7 || $nbSleptCycles > 0) {
            return;
        }

        $this->updatePlayerStatisticService->execute(
            player: $player,
            statisticName: StatisticEnum::GAME_WITHOUT_SLEEP,
        );
    }

    public function onPlayerDeath(PlayerEvent $event): void
    {
        $this->incrementMushKilledStats($event);
        $this->attributeLastManStanding($event);
    }

    public function onPlayerGotLiked(PlayerEvent $event): void
    {
        $player = $event->getPlayer();

        $this->updatePlayerStatisticService->execute(
            player: $player,
            statisticName: StatisticEnum::LIKES,
        );
    }

    public function onTitleRemoved(PlayerEvent $event): void
    {
        // Don't attribute if it was not Commander
        if ($event->doesNotHaveTag(TitleEnum::COMMANDER)) {
            return;
        }

        // Don't attribute if the ship is going with living players ($event->getPlayer() is still marked as alive)
        if (!$event->getDaedalus()->getDaedalusInfo()->isDaedalusFinished()
            && $event->getDaedalus()->getAlivePlayers()->count() > 1) {
            return;
        }

        // Don't attribute if player did not die
        if (!array_intersect($event->getTags(), EndCauseEnum::getDeathEndCauses()->toArray())) {
            return;
        }

        $this->updatePlayerStatisticService->execute(
            player: $event->getPlayer(),
            statisticName: StatisticEnum::COMMANDER_SHOULD_GO_LAST,
        );
    }

    private function incrementMushKilledStats(PlayerEvent $event): void
    {
        // Don't increment stats if death did not result from killing
        if (!$this->isAboutAssassination($event)) {
            return;
        }

        // Don't increment stats if the dying player is not Mush
        if ($event->getPlayer()->isHuman()) {
            return;
        }

        $killer = $event->getAuthor();

        // Don't increment stats if none of the crew killed
        if (!$killer) {
            return;
        }

        foreach ($event->getDaedalus()->getAlivePlayers()->getHumanPlayer() as $player) {
            $this->updatePlayerStatisticService->execute(
                player: $player,
                statisticName: StatisticEnum::TEAM_MUSH_KILLED,
            );

            if ($killer === $player) {
                $this->updatePlayerStatisticService->execute(
                    player: $player,
                    statisticName: StatisticEnum::MUSH_KILLED,
                );

                if ($event->hasTag(ItemEnum::NATAMY_RIFLE)) {
                    $this->updatePlayerStatisticService->execute(
                        player: $player,
                        statisticName: StatisticEnum::NATAMIST,
                    );
                }
            }
        }
    }

    private function attributeLastManStanding(PlayerEvent $event): void
    {
        $player = $event->getPlayer();

        // Don't attribute if the player did not die
        if (EndCauseEnum::isNotDeathEndCause($player->getPlayerInfo()->getClosedPlayer()->getEndCause())) {
            return;
        }

        $daedalus = $event->getDaedalus();

        // Don't attribute if Daedalus is still ongoing
        if (!$daedalus->getDaedalusInfo()->isDaedalusFinished()) {
            return;
        }

        // Don't attribute if the player is not the last to die
        if (!$daedalus->getAlivePlayers()->isEmpty()) {
            return;
        }

        $this->updatePlayerStatisticService->execute(
            player: $player,
            statisticName: StatisticEnum::LAST_MEMBER,
        );
    }

    private function isAboutAssassination(PlayerEvent $event): bool
    {
        return $event->hasAnyTag([
            EndCauseEnum::ASSASSINATED,
            EndCauseEnum::BEHEADED,
            EndCauseEnum::BLED,
            EndCauseEnum::INJURY,
            EndCauseEnum::ROCKETED, ]);
    }
}
