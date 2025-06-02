<?php

namespace Mush\Daedalus\Listener;

use Mush\Daedalus\Repository\DaedalusRepositoryInterface;
use Mush\Exploration\Event\ExplorationEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ExplorationStartedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private DaedalusRepositoryInterface $daedalusRepository,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ExplorationEvent::EXPLORATION_STARTED => 'onExplorationStarted',
        ];
    }

    public function onExplorationStarted(ExplorationEvent $event): void
    {
        $event->getDaedalusStatistics()->changeExplorationsStarted(1);

        $this->daedalusRepository->save($event->getDaedalus());
    }
}
