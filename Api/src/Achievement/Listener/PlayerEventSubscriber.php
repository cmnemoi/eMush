<?php

declare(strict_types=1);

namespace Mush\Achievement\Listener;

use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Services\UpdatePlayerStatisticService;
use Mush\Game\Enum\EventPriorityEnum;
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
            PlayerEvent::END_PLAYER => ['onPlayerEnd', EventPriorityEnum::LOWEST],
            PlayerEvent::DEATH_PLAYER => ['onDeathPlayer', EventPriorityEnum::LOWEST],
            PlayerEvent::PLAYER_GOT_LIKED => ['onPlayerGotLiked', EventPriorityEnum::LOWEST],
        ];
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

    public function onDeathPlayer(PlayerEvent $event): void
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
            }
        }
    }

    public function onPlayerGotLiked(PlayerEvent $event): void
    {
        $player = $event->getPlayer();

        $this->updatePlayerStatisticService->execute(
            player: $player,
            statisticName: StatisticEnum::LIKES,
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
