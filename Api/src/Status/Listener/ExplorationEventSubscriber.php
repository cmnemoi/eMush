<?php

declare(strict_types=1);

namespace Mush\Status\Listener;

use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Exploration\Event\ExplorationEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ExplorationEventSubscriber implements EventSubscriberInterface
{
    private EventServiceInterface $eventService;
    private StatusServiceInterface $statusService;

    public function __construct(EventServiceInterface $eventService, StatusServiceInterface $statusService)
    {
        $this->eventService = $eventService;
        $this->statusService = $statusService;
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
        $explorators = $exploration->getExplorators();
        $planet = $exploration->getPlanet();

        // no need to block explorators if there is oxygen on the planet
        if ($planet->hasSectorByName(PlanetSectorEnum::OXYGEN)) {
            return;
        }

        $exploratorsWithoutSpaceSuit = $exploration->getExploratorsWithoutSpacesuit();

        /** @var Player $explorator */
        foreach ($exploratorsWithoutSpaceSuit as $explorator) {
            $this->statusService->createStatusFromName(
                statusName: PlayerStatusEnum::STUCK_IN_THE_SHIP,
                holder: $explorator,
                tags: $event->getTags(),
                time: $event->getTime(),
                visibility: VisibilityEnum::PUBLIC,
            );
        }

        // won't do an exploration with all explorators stucked in the ship
        if ($exploratorsWithoutSpaceSuit->count() === $explorators->count()) {
            $event->stopPropagation();

            $explorationEvent = new ExplorationEvent(
                exploration: $exploration,
                tags: $event->getTags(),
                time: new \DateTime(),
            );
            $explorationEvent->addTag(ExplorationEvent::ALL_EXPLORATORS_STUCKED);
            $this->eventService->callEvent($explorationEvent, ExplorationEvent::ALL_EXPLORATORS_STUCKED);
        }
    }

    public function onExplorationFinished(ExplorationEvent $event): void
    {
        $exploratorsWithoutSpaceSuit = $event->getExploration()->getExploratorsWithoutSpacesuit();

        /** @var Player $explorator */
        foreach ($exploratorsWithoutSpaceSuit as $explorator) {
            $this->statusService->removeStatus(
                statusName: PlayerStatusEnum::STUCK_IN_THE_SHIP,
                holder: $explorator,
                tags: $event->getTags(),
                time: $event->getTime(),
            );
        }
    }
}
