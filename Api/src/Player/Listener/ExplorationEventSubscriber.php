<?php

declare(strict_types=1);

namespace Mush\Player\Listener;

use Mush\Exploration\Event\ExplorationEvent;
use Mush\Place\Enum\RoomEnum;
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
        // @TODO: some explorations do not return to Icarus bay, we need to handle that.
        $icarusBay = $exploration->getDaedalus()->getPlaceByName(RoomEnum::ICARUS_BAY);
        if (!$icarusBay) {
            throw new \RuntimeException('There should be one Icarus bay in Daedalus');
        }

        $explorators = $exploration->getExplorators();
        foreach ($explorators as $explorator) {
            $this->playerService->changePlace($explorator, $icarusBay);
        }
    }
}
