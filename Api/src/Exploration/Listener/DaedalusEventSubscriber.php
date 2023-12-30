<?php

declare(strict_types=1);

namespace Mush\Exploration\Listener;

use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Exploration\Entity\Planet;
use Mush\Exploration\Event\ExplorationEvent;
use Mush\Exploration\Service\ExplorationServiceInterface;
use Mush\Exploration\Service\PlanetServiceInterface;
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
            DaedalusEvent::TRAVEL_LAUNCHED => 'onTravelLaunched',
        ];
    }

    public function onTravelLaunched(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();

        $exploration = $daedalus->getExploration();

        // If daedalus leaves while exploration is ongoing, all explorators will die
        if ($exploration) {
            $this->explorationService->closeExploration($exploration, [ExplorationEvent::ALL_EXPLORATORS_ARE_DEAD]);
        }

        $planetsToDelete = $this->planetService->findAllByDaedalus($daedalus)->filter(
            fn (Planet $planet) => !$planet->getCoordinates()->equals($daedalus->getDestination())
        );

        $this->planetService->delete($planetsToDelete->toArray());
    }
}
