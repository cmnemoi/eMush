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
            $this->eventService->callEvent(new ProjectEvent(...$event->toArray()), ProjectEvent::PROJECT_FINISHED);
        }
    }
}
