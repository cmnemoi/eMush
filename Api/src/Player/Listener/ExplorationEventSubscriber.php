<?php

declare(strict_types=1);

namespace Mush\Player\Listener;

use Mush\Exploration\Event\ExplorationEvent;
use Mush\Player\Service\PlayerServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ExplorationEventSubscriber implements EventSubscriberInterface
{
    private PlayerServiceInterface $playerService;

    public function __construct(PlayerServiceInterface $playerService)
    {
        $this->playerService = $playerService;
    }

    public static function getSubscribedEvents()
    {
        return [
            ExplorationEvent::EXPLORATION_STARTED => 'onExplorationStarted',
        ];
    }

    public function onExplorationStarted(ExplorationEvent $event): void
    {
        $exploration = $event->getExploration();
        $planetPlace = $exploration->getDaedalus()->getPlanetPlace();

        $explorators = $exploration->getExplorators();
        foreach ($explorators as $explorator) {
            $this->playerService->changePlace($explorator, $planetPlace);
        }
    }
}
