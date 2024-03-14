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
            ExplorationEvent::EXPLORATION_FINISHED => 'onExplorationFinished',
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

    public function onExplorationFinished(ExplorationEvent $event): void
    {
        $exploration = $event->getExploration();
        $returnPlace = $exploration->getDaedalus()->getPlaceByName($exploration->getStartPlaceName());
        if (!$returnPlace) {
            throw new \RuntimeException("There is no place with name {$exploration->getStartPlaceName()} in this Daedalus");
        }

        $explorators = $exploration->getNotLostExplorators();
        foreach ($explorators as $explorator) {
            $this->playerService->changePlace($explorator, $returnPlace);
        }
    }
}
