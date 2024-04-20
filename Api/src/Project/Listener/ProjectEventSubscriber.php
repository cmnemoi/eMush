<?php

declare(strict_types=1);

namespace Mush\Project\Listener;

use Mush\Game\Service\EventServiceInterface;
use Mush\Project\Event\ProjectEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ProjectEventSubscriber implements EventSubscriberInterface
{
    private EventServiceInterface $eventService;

    public function __construct(EventServiceInterface $eventService)
    {
        $this->eventService = $eventService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProjectEvent::PROJECT_ADVANCED => 'onProjectAdvanced',
        ];
    }

    public function onProjectAdvanced(ProjectEvent $event): void
    {
        if ($event->projectIsFinished()) {
            $projectEvent = new ProjectEvent(
                $event->getProject(),
                $event->getAuthor(),
                $event->getTags(),
                $event->getTime(),
            );
            $this->eventService->callEvent($projectEvent, ProjectEvent::PROJECT_FINISHED);
        }
    }
}
