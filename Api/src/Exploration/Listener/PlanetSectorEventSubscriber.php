<?php

declare(strict_types=1);

namespace Mush\Exploration\Listener;

use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Exploration\Service\PlanetSectorEventHandlerServiceInterface;
use Mush\Game\Enum\EventPriorityEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class PlanetSectorEventSubscriber implements EventSubscriberInterface
{
    private PlanetSectorEventHandlerServiceInterface $planetSectorEventHandlerService;

    public function __construct(
        PlanetSectorEventHandlerServiceInterface $planetSectorEventHandlerService,
    ) {
        $this->planetSectorEventHandlerService = $planetSectorEventHandlerService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PlanetSectorEvent::PLANET_SECTOR_EVENT => [
                ['handlePlanetSectorEvent', EventPriorityEnum::NORMAL],
                ['resetSabotage', EventPriorityEnum::LOWEST],
            ],
        ];
    }

    public function handlePlanetSectorEvent(PlanetSectorEvent $event): void
    {
        $planetSectorEventHandler = $this->planetSectorEventHandlerService->getPlanetSectorEventHandler($event->getName());

        if ($planetSectorEventHandler === null) {
            throw new \RuntimeException("PlanetSectorEventHandler not found for event: {$event->getName()}");
        }

        $planetSectorEventHandler->handle($event);
    }

    public function resetSabotage(PlanetSectorEvent $event): void
    {
        if ($event->getExploration()->isSabotaged()) {
            $event->getExploration()->setIsSabotaged(false);
        }
    }
}
