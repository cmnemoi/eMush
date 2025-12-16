<?php

declare(strict_types=1);

namespace Mush\Achievement\Listener;

use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Services\UpdatePlayerStatisticService;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Player\Enum\EndCauseEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class PlanetSectorEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private UpdatePlayerStatisticService $updatePlayerStatisticService,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            PlanetSectorEvent::PLANET_SECTOR_EVENT => ['onPlanetSectorEvent', EventPriorityEnum::VERY_LOW],
        ];
    }

    public function onPlanetSectorEvent(PlanetSectorEvent $event): void
    {
        if ($event->hasAllTags([PlanetSectorEvent::FIGHT_WON, EndCauseEnum::MANKAROG])) {
            foreach ($event->getDaedalus()->getAlivePlayers() as $player) {
                $this->updatePlayerStatisticService->execute(
                    player: $player,
                    statisticName: StatisticEnum::MANKAROG_DOWN,
                );
            }
        }
    }
}
