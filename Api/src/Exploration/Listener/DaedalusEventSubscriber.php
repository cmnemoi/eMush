<?php

declare(strict_types=1);

namespace Mush\Exploration\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Exploration\Entity\Exploration;
use Mush\Exploration\Entity\Planet;
use Mush\Exploration\Service\ExplorationServiceInterface;
use Mush\Exploration\Service\PlanetServiceInterface;
use Mush\Game\Enum\EventPriorityEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class DaedalusEventSubscriber implements EventSubscriberInterface
{
    private PlanetServiceInterface $planetService;
    private ExplorationServiceInterface $explorationService;

    public function __construct(
        PlanetServiceInterface $planetService,
        ExplorationServiceInterface $explorationService
    ) {
        $this->planetService = $planetService;
        $this->explorationService = $explorationService;
    }

    public static function getSubscribedEvents()
    {
        return [
            DaedalusEvent::FINISH_DAEDALUS => ['onFinishDaedalus', EventPriorityEnum::LOW],
            DaedalusEvent::TRAVEL_LAUNCHED => 'onTravelLaunched',
        ];
    }

    public function onFinishDaedalus(DaedalusEvent $event): void
    {
        $exploration = $event->getDaedalus()->getExploration();
        if ($exploration === null) {
            return;
        }

        $event->addTag(DaedalusEvent::FINISH_DAEDALUS);
        $this->explorationService->closeExploration($exploration, $event->getTags());
    }

    public function onTravelLaunched(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();

        /** @var Exploration $exploration */
        $exploration = $daedalus->getExploration();

        if ($daedalus->hasAnOngoingExploration()) {
            $this->explorationService->closeExploration($exploration, $event->getTags());
        }

        $this->deletePlanets($event);
    }

    private function deletePlanets(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();

        // delete all planets by default
        $planetsToDelete = $this->planetService->findAllByDaedalus($daedalus);

        // If daedalus is not leaving orbit, do not delete the planet where the Daedalus wants to go
        if (!$event->hasTag(ActionEnum::LEAVE_ORBIT->value)) {
            $planetsToDelete = $planetsToDelete->filter(
                static fn (Planet $planet) => !$planet->getCoordinates()->equals($daedalus->getDestination())
            );
        }

        $this->planetService->delete($planetsToDelete->toArray());
    }
}
