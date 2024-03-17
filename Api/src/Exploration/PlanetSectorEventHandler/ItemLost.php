<?php

declare(strict_types=1);

namespace Mush\Exploration\PlanetSectorEventHandler;

use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Exploration\Entity\ExplorationLog;
use Mush\Exploration\Entity\PlanetSectorEventConfig;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;

final class ItemLost extends AbstractPlanetSectorEventHandler
{
    public function getName(): string
    {
        return PlanetSectorEvent::ITEM_LOST;
    }

    public function handle(PlanetSectorEvent $event): ExplorationLog
    {
        $availableItemsToDestroy = $this->getAvailableItemsToDestroy($event->getExploration()->getActiveAndNotLostExplorators());
        if (empty($availableItemsToDestroy)) {
            $this->dispatchNothingToReportEvent($event);

            return new ExplorationLog($event->getExploration()->getClosedExploration());
        }

        /** @var GameItem $itemToDestroy */
        $itemToDestroy = $this->randomService->getRandomElement($availableItemsToDestroy);
        /** @var Player $itemOwner */
        $itemOwner = $itemToDestroy->getHolder();

        $interactEvent = new InteractWithEquipmentEvent(
            $itemToDestroy,
            $itemOwner,
            VisibilityEnum::PUBLIC,
            $event->getTags(),
            $event->getTime(),
        );
        $this->eventService->callEvent($interactEvent, EquipmentEvent::EQUIPMENT_DESTROYED);

        $logParameters = [
            $itemToDestroy->getLogKey() => $itemToDestroy->getLogName(),
            $itemOwner->getLogKey() => $itemOwner->getLogName(),
        ];

        return $this->createExplorationLog($event, $logParameters);
    }

    private function getAvailableItemsToDestroy(PlayerCollection $explorators): array
    {
        $availableItems = [];
        /** @var Player $explorator */
        foreach ($explorators as $explorator) {
            /** @var GameItem $item */
            foreach ($explorator->getEquipments() as $item) {
                if ($item->getName() !== GearItemEnum::SPACESUIT) {
                    $availableItems[] = $item;
                }
            }
        }

        return $availableItems;
    }

    private function dispatchNothingToReportEvent(PlanetSectorEvent $event): void
    {
        $config = new PlanetSectorEventConfig();
        $config->setName(PlanetSectorEvent::NOTHING_TO_REPORT);
        $config->setEventName(PlanetSectorEvent::NOTHING_TO_REPORT);

        $nothingToReportEvent = new PlanetSectorEvent(
            $event->getPlanetSector(),
            $config,
            $event->getTags(),
            $event->getTime(),
            $event->getVisibility()
        );
        $this->eventService->callEvent($nothingToReportEvent, PlanetSectorEvent::PLANET_SECTOR_EVENT);
    }
}
