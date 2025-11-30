<?php

declare(strict_types=1);

namespace Mush\Achievement\Listener;

use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Services\UpdatePlayerStatisticService;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Player\Event\PlayerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class PlayerEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private UpdatePlayerStatisticService $updatePlayerStatisticService) {}

    public static function getSubscribedEvents(): array
    {
        return [
            PlayerEvent::END_PLAYER => ['onPlayerEnd', EventPriorityEnum::LOWEST],
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

    public function onPlayerGotLiked(PlayerEvent $event): void
    {
        $player = $event->getPlayer();

        $this->updatePlayerStatisticService->execute(
            player: $player,
            statisticName: StatisticEnum::LIKES,
        );
    }
}
