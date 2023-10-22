<?php

declare(strict_types=1);

namespace Mush\Exploration\Listener;

use Mush\Exploration\Event\ExplorationEvent;
use Mush\Exploration\Service\ExplorationServiceInterface;
use Mush\Game\Enum\EventPriorityEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ExplorationEventSubscriber implements EventSubscriberInterface
{
    private ExplorationServiceInterface $explorationService;

    public function __construct(ExplorationServiceInterface $explorationService)
    {
        $this->explorationService = $explorationService;
    }

    public static function getSubscribedEvents()
    {
        return [
            ExplorationEvent::EXPLORATION_STARTED => ['onExplorationStarted', EventPriorityEnum::LOWEST],
        ];
    }

    public function onExplorationStarted(ExplorationEvent $event): void
    {
        $this->explorationService->computeExplorationEvents($event->getExploration());
    }

}