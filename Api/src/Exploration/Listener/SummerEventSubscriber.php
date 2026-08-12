<?php

declare(strict_types=1);

namespace Mush\Exploration\Listener;

use Mush\Exploration\Enum\PlanetConfigsEnum;
use Mush\Exploration\Event\ExplorationEvent;
use Mush\Exploration\Service\PlanetServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class SummerEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private PlanetServiceInterface $planetService, private StatusServiceInterface $statusService, private RoomLogServiceInterface $roomLogService) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ExplorationEvent::EXPLORATION_FINISHED => ['onExplorationFinished', -10000],
        ];
    }

    public function onExplorationFinished(ExplorationEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $status = $daedalus->getChargeStatusByName(DaedalusStatusEnum::IN_ORBIT_OF_EVENT_PLANET);

        if ($status === null) {
            return;
        }

        $planet = $this->planetService->findPlanetInDaedalusOrbit($daedalus);
        if ($planet === null) {
            throw new \Exception('If in orbit of summer event planet, daedalus should be in orbit of a planet.');
        }

        $this->statusService->updateCharge($status, 1, $event->getTags(), $event->getTime());
        $this->planetService->regenerateAPlanet($planet, $this->getPlanetConfig($status->getCharge()));

        foreach ($event->getExploration()->getExplorators() as $player) {
            $this->roomLogService->createLog(
                LogEnum::SUMMER_EVENT_EXPLORATION_LOG,
                $player->getPlace(),
                VisibilityEnum::PRIVATE,
                'event_log',
                $player,
            );
        }
    }

    private function getPlanetConfig(int $charge): string
    {
        return match ($charge) {
            1 => PlanetConfigsEnum::SUMMER_EVENT_1,
            2 => PlanetConfigsEnum::SUMMER_EVENT_2,
            3 => PlanetConfigsEnum::SUMMER_EVENT_3,
            4 => PlanetConfigsEnum::SUMMER_EVENT_4,
            default => PlanetConfigsEnum::SUMMER_EVENT_5,
        };
    }
}
