<?php

declare(strict_types=1);

namespace Mush\Equipment\Listener;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\DeleteEquipmentServiceInterface;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Exploration\Event\ExplorationEvent;
use Mush\Game\Enum\EventPriorityEnum;
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
        $daedalus = $event->getDaedalus();
        $exploration = $event->getExploration();

        // No one can pilot the ship back. Everything on the planet is destroyed except the icarus if the project Magnetic Return is done.
        if ($exploration->allExploratorsAreDeadOrLost()) {
            $ship = $daedalus->getPlanetPlace()->getEquipmentByNameOrThrow($exploration->getShipUsedName());

            // If the ship is not the icarus and the Magnetic Return is not done then it is deleted.
            if ($ship->getName() !== EquipmentEnum::ICARUS || $daedalus->doesNotHaveAutoReturnIcarusProject()) {
                $this->deleteEquipmentService->execute($ship, tags: $event->getTags(), time: $event->getTime());
            } else {
                $this->gameEquipmentService->moveEquipmentTo(equipment: $ship, newHolder: $event->getStartPlace(), tags: $event->getTags(), time: $event->getTime());
            }

            // Destroy everything else on the planet.
            foreach ($daedalus->getPlanetPlace()->getEquipments() as $equipment) {
                $this->deleteEquipmentService->execute(gameEquipment: $equipment, tags: $event->getTags(), time: $event->getTime());
            }

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
