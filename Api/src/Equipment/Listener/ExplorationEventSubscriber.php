<?php

declare(strict_types=1);

namespace Mush\Equipment\Listener;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Exploration\Event\ExplorationEvent;
use Mush\Game\Enum\EventPriorityEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class ExplorationEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private GameEquipmentServiceInterface $gameEquipmentService) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ExplorationEvent::EXPLORATION_STARTED => ['onExplorationStarted', EventPriorityEnum::HIGH],
            ExplorationEvent::EXPLORATION_FINISHED => ['onExplorationFinished', EventPriorityEnum::HIGH],
        ];
    }

    public function onExplorationStarted(ExplorationEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $exploration = $event->getExploration();
        $startPlace = $event->getStartPlace();

        $this->gameEquipmentService->moveEquipmentTo(
            equipment: $startPlace->getEquipmentByNameOrThrow($exploration->getShipUsedName()),
            newHolder: $daedalus->getPlanetPlace(),
            tags: $event->getTags(),
            time: $event->getTime(),
        );
    }

    public function onExplorationFinished(ExplorationEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $exploration = $event->getExploration();

        // All explorators are dead, all equipment stay on the planet! Unless Daedalus has the Auto Return Icarus project.
        if ($exploration->allExploratorsAreDead() && $daedalus->doesNotHaveAutoReturnIcarusProject()) {
            return;
        }

        /** @var GameEquipment $equipment */
        foreach ($daedalus->getPlanetPlace()->getEquipments() as $equipment) {
            $this->gameEquipmentService->moveEquipmentTo(
                equipment: $equipment,
                newHolder: $event->getStartPlace(),
                tags: $event->getTags(),
                time: $event->getTime(),
            );
        }
    }
}
