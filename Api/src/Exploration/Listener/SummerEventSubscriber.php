<?php

declare(strict_types=1);

namespace Mush\Exploration\Listener;

use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Exploration\Enum\PlanetConfigsEnum;
use Mush\Exploration\Enum\PlanetSectorEnum;
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
    public function __construct(
        private PlanetServiceInterface $planetService,
        private StatusServiceInterface $statusService,
        private RoomLogServiceInterface $roomLogService,
        private GameEquipmentServiceInterface $gameEquipmentService
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ExplorationEvent::EXPLORATION_FINISHED => ['onExplorationFinished', -10000],
        ];
    }

    public function onExplorationFinished(ExplorationEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $InOrbitStatus = $daedalus->getChargeStatusByName(DaedalusStatusEnum::IN_ORBIT_OF_EVENT_PLANET);

        if ($InOrbitStatus === null) {
            return;
        }

        $planet = $this->planetService->findPlanetInDaedalusOrbit($daedalus);
        if ($planet === null) {
            throw new \Exception('If in orbit of summer event planet, daedalus should be in orbit of a planet.');
        }

        if ($planet->getUnvisitedSectors()->isEmpty() === false) {
            return; // we don't want te remove lost or sectors important to the event. They can only regenerate the planet if they see all sectors.
        }

        // get where the treasure is or -1
        $treasureStatus = $daedalus->getChargeStatusByName(DaedalusStatusEnum::TREASURE_SECTOR);
        $treasureSectorCharge = $treasureStatus ? $treasureStatus->getCharge() : -1;

        // increase the difficulty of the planet before regenerating it
        $this->statusService->updateCharge($InOrbitStatus, 1, $event->getTags(), $event->getTime());

        $this->planetService->regenerateAPlanet(
            $planet,
            $this->getPlanetConfig($InOrbitStatus->getCharge()),
            $this->getForcedSector($InOrbitStatus->getCharge(), $treasureSectorCharge)
        );

        // if treasure is not found, make a log that signal that the planet can be explored again.
        if (
            $this->gameEquipmentService->findByNameAndDaedalus(ItemEnum::TREASURE_HUNT_CHEST_CLOSED, $daedalus)->isEmpty()
            && $this->gameEquipmentService->findByNameAndDaedalus(ItemEnum::TREASURE_HUNT_CHEST_OPENED, $daedalus)->isEmpty()
        ) {
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

    private function getForcedSector(int $eventPlanetCharge, int $treasureSectorCharge): ?string
    {
        return match ($eventPlanetCharge) {
            1 => PlanetSectorEnum::TREASURE_HUNT_PET,
            2 => PlanetSectorEnum::TREASURE_HUNT_SHIP,
            default => PlanetSectorEnum::getTreasureSectorFromCharge($treasureSectorCharge),
        };
    }
}
