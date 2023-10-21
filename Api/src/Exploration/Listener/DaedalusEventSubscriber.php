<?php

declare(strict_types=1);

namespace Mush\Exploration\Listener;

use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Exploration\Entity\Planet;
use Mush\Exploration\Service\PlanetServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class DaedalusEventSubscriber implements EventSubscriberInterface
{
    private PlanetServiceInterface $planetService;

    public function __construct(
        PlanetServiceInterface $planetService
    ) {
        $this->planetService = $planetService;
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

        $planetsToDelete = $this->planetService->findAllByDaedalus($daedalus)->filter(
            fn (Planet $planet) => !$planet->getCoordinates()->equals($daedalus->getDestination())
        );

        $this->planetService->delete($planetsToDelete->toArray());
    }
}
