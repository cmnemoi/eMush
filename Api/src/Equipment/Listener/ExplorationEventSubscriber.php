<?php

declare(strict_types=1);

namespace Mush\Equipment\Listener;

use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\MoveEquipmentEvent;
use Mush\Exploration\Event\ExplorationEvent;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Player;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ExplorationEventSubscriber implements EventSubscriberInterface
{
    private EventServiceInterface $eventService;

    public function __construct(EventServiceInterface $eventService)
    {
        $this->eventService = $eventService;
    }

    public static function getSubscribedEvents()
    {
        return [
            ExplorationEvent::EXPLORATION_STARTED => ['onExplorationStarted', EventPriorityEnum::HIGH],
            ExplorationEvent::EXPLORATION_FINISHED => ['onExplorationFinished', EventPriorityEnum::HIGH],
        ];
    }

    public function onExplorationStarted(ExplorationEvent $event): void
    {
        $exploration = $event->getExploration();
        /** @var Player $explorator */
        $explorator = $exploration->getExplorators()->first();
        if (!$explorator) {
            throw new \RuntimeException('You need a non-empty explorator collection to create an exploration');
        }

        // explorators are still in Icarus bay at this point, so recover Icarus ship on the room this way
        $icarus = $explorator->getPlace()->getEquipmentByName(EquipmentEnum::ICARUS);
        if (!$icarus) {
            throw new \RuntimeException('There should be one Icarus ship in Icarus bay');
        }

        $equipmentEvent = new MoveEquipmentEvent(
            equipment: $icarus,
            newHolder: $exploration->getDaedalus()->getPlanetPlace(),
            author: null,
            visibility: VisibilityEnum::HIDDEN,
            tags: $event->getTags(),
            time: $event->getTime(),
        );
        $this->eventService->callEvent($equipmentEvent, EquipmentEvent::CHANGE_HOLDER);
    }

    public function onExplorationFinished(ExplorationEvent $event): void
    {
        $exploration = $event->getExploration();
        /** @var Player $explorator */
        $explorator = $exploration->getExplorators()->getPlayerAlive()->first();

        // All explorators are dead, no Icarus return!
        // @TODO : Magnetic return project should prevent this
        if (!$explorator) {
            return;
        }

        $icarus = $explorator->getPlace()->getEquipmentByName(EquipmentEnum::ICARUS);
        if (!$icarus) {
            throw new \RuntimeException('There should be one Icarus ship in explorator place');
        }

        // @TODO: some explorations do not start in Icarus Bay, we need to handle that.
        $icarusBay = $exploration->getDaedalus()->getPlaceByName(RoomEnum::ICARUS_BAY);
        if (!$icarusBay) {
            throw new \RuntimeException('There should be one Icarus bay in Daedalus');
        }

        $equipmentEvent = new MoveEquipmentEvent(
            equipment: $icarus,
            newHolder: $icarusBay,
            author: null,
            visibility: VisibilityEnum::HIDDEN,
            tags: $event->getTags(),
            time: $event->getTime(),
        );
        $this->eventService->callEvent($equipmentEvent, EquipmentEvent::CHANGE_HOLDER);
    }
}
