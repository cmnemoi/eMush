<?php

declare(strict_types=1);

namespace Mush\Equipment\Listener;

use Mush\Equipment\Service\DeleteEquipmentServiceInterface;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Exploration\Event\ExplorationEvent;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Project\Enum\ProjectName;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class ExplorationEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private GameEquipmentServiceInterface $gameEquipmentService,
        private DeleteEquipmentServiceInterface $deleteEquipmentService
    ) {}

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
        $exploration = $event->getExploration();

        if ($exploration->allExploratorsAreDeadOrLost()) {
            $this->destroyExplorationShipOrReturnIt($event);
            $this->destroyRemainingPlanetEquipment($event);
        } else {
            $this->returnPlanetEquipmentToDaedalus($event);
        }
    }

    private function destroyExplorationShipOrReturnIt(ExplorationEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $exploration = $event->getExploration();
        $explorationShip = $daedalus->getPlanetPlace()->getEquipmentByNameOrThrow($exploration->getShipUsedName());

        if (ProjectName::AUTO_RETURN_ICARUS->shouldReturnShipToDaedalus($explorationShip, $daedalus)) {
            $this->gameEquipmentService->moveEquipmentTo(
                equipment: $explorationShip,
                newHolder: $event->getStartPlace(),
                tags: $event->getTags(),
                time: $event->getTime()
            );
        } else {
            $this->deleteEquipmentService->execute(
                gameEquipment: $explorationShip,
                tags: $event->getTags(),
                time: $event->getTime()
            );
        }
    }

    private function destroyRemainingPlanetEquipment(ExplorationEvent $event): void
    {
        $daedalus = $event->getDaedalus();

        foreach ($daedalus->getPlanetPlace()->getEquipments() as $equipment) {
            $this->deleteEquipmentService->execute(
                gameEquipment: $equipment,
                tags: $event->getTags(),
                time: $event->getTime()
            );
        }
    }

    private function returnPlanetEquipmentToDaedalus(ExplorationEvent $event): void
    {
        $daedalus = $event->getDaedalus();

        foreach ($daedalus->getPlanetPlace()->getEquipments() as $equipment) {
            $this->gameEquipmentService->moveEquipmentTo(
                equipment: $equipment,
                newHolder: $event->getStartPlace(),
                tags: $event->getTags(),
                time: $event->getTime()
            );
        }
    }
}
