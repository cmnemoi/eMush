<?php

declare(strict_types=1);

namespace Mush\Daedalus\Listener;

use Mush\Game\Service\EventServiceInterface;
use Mush\Project\Event\ProjectEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class ProjectEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EventServiceInterface $eventService,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ProjectEvent::PROJECT_FINISHED => 'onProjectFinished',
        ];
    }

    public function onProjectFinished(ProjectEvent $event): void
    {
        $event->getDaedalusProjectsStatistics()->addCompletedProject($event->getProject());
    }
}
