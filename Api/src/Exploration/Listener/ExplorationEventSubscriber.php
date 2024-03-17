<?php

declare(strict_types=1);

namespace Mush\Exploration\Listener;

use Mush\Exploration\Entity\Exploration;
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
            ExplorationEvent::ALL_EXPLORATORS_STUCKED => 'onAllExploratorsStucked',
            ExplorationEvent::EXPLORATION_STARTED => ['onExplorationStarted', EventPriorityEnum::LOWEST],
            ExplorationEvent::EXPLORATION_NEW_CYCLE => ['onExplorationNewCycle', EventPriorityEnum::LOWEST],
        ];
    }

    public function onAllExploratorsStucked(ExplorationEvent $event): void
    {
        $this->explorationService->closeExploration($event->getExploration(), $event->getTags());
    }

    public function onExplorationStarted(ExplorationEvent $event): void
    {
        $exploration = $this->explorationService->dispatchLandingEvent($event->getExploration());

        $exploration->incrementCycle();
        $this->explorationService->persist([$exploration]);

        $this->closeExplorationPrematurelyIfNeeded($exploration);
    }

    public function onExplorationNewCycle(ExplorationEvent $event): void
    {
        $exploration = $this->explorationService->dispatchExplorationEvent($event->getExploration());

        $exploration->incrementCycle();
        $this->explorationService->persist([$exploration]);

        $this->closeExplorationPrematurelyIfNeeded($exploration);
    }

    private function closeExplorationPrematurelyIfNeeded(Exploration $exploration): void
    {
        $allNonLostExploratorsAreDead = $exploration->getActiveAndNotLostExplorators()->isEmpty();
        $allSectorsVisited = $exploration->getCycle() >= $exploration->getNumberOfSectionsToVisit() + 1;

        if ($allNonLostExploratorsAreDead) {
            $this->explorationService->closeExploration($exploration, [ExplorationEvent::ALL_EXPLORATORS_ARE_DEAD]);
        } elseif ($allSectorsVisited) {
            $this->explorationService->closeExploration($exploration, [ExplorationEvent::ALL_SECTORS_VISITED]);
        }
    }
}
