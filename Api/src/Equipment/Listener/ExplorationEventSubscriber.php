<?php

declare(strict_types=1);

namespace Mush\Equipment\Listener;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\MoveEquipmentEvent;
use Mush\Exploration\Event\ExplorationEvent;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ExplorationEventSubscriber implements EventSubscriberInterface
{
    private EventServiceInterface $eventService;

    public function __construct(EventServiceInterface $eventService)
    {
        $this->eventService = $eventService;
    }

    public static function getSubscribedEvents(): array
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

        $explorationShip = $explorator->getPlace()->getEquipmentByName($exploration->getShipUsedName());
        if (!$explorationShip) {
            throw new \RuntimeException("There should be a {$exploration->getShipUsedName()} ship in explorator's place");
        }

        $equipmentEvent = new MoveEquipmentEvent(
            equipment: $explorationShip,
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
        $daedalus = $exploration->getDaedalus();

        // All explorators are dead, all equipment stay on the planet!
        // @TODO : Magnetic return project should prevent this for the Icarus
        if (!$exploration->isAnyExploratorAlive()) {
            return;
        }

        $returnPlace = $daedalus->getPlaceByName($exploration->getStartPlaceName());
        if (!$returnPlace) {
            throw new \RuntimeException("There should be a {$exploration->getStartPlaceName()} place in Daedalus");
        }

        /** @var GameEquipment $equipment */
        foreach ($daedalus->getPlanetPlace()->getEquipments() as $equipment) {
            $equipmentEvent = new MoveEquipmentEvent(
                equipment: $equipment,
                newHolder: $returnPlace,
                author: null,
                visibility: VisibilityEnum::HIDDEN,
                tags: $event->getTags(),
                time: $event->getTime(),
            );
            $this->eventService->callEvent($equipmentEvent, EquipmentEvent::CHANGE_HOLDER);
        }
    }
}
