<?php

declare(strict_types=1);

namespace Mush\Exploration\Listener;

use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Exploration\Service\ExplorationServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class PlanetSectorEventSubscriber implements EventSubscriberInterface
{
    private ExplorationServiceInterface $explorationService;

    public function __construct(
        ExplorationServiceInterface $explorationService,
    ) {
        $this->explorationService = $explorationService;
    }

    public static function getSubscribedEvents()
    {
        return [
            PlanetSectorEvent::ACCIDENT => 'onAccident',
            PlanetSectorEvent::DISASTER => 'onDisaster',
            PlanetSectorEvent::NOTHING_TO_REPORT => 'onNothingToReport',
            PlanetSectorEvent::TIRED => 'onTired',
        ];
    }

    public function onAccident(PlanetSectorEvent $event): void
    {
        $logParameters = $this->explorationService->removeHealthToARandomExplorator($event);

        $this->explorationService->createExplorationLog($event, $logParameters);
    }

    public function onDisaster(PlanetSectorEvent $event): void
    {
        $logParameters = $this->explorationService->removeHealthToAllExplorators($event);

        $this->explorationService->createExplorationLog($event, $logParameters);
    }

    public function onNothingToReport(PlanetSectorEvent $event): void
    {
        $this->explorationService->createExplorationLog($event);
    }

    public function onTired(PlanetSectorEvent $event): void
    {
        $logParameters = $this->explorationService->removeHealthToAllExplorators($event);

        $this->explorationService->createExplorationLog($event, $logParameters);
    }
}
