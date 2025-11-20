<?php

declare(strict_types=1);

namespace Mush\RoomLog\Listener;

use Mush\Game\Enum\VisibilityEnum;
use Mush\Project\Event\ProjectEvent;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ProjectEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private RoomLogServiceInterface $roomLogService) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ProjectEvent::PROJECT_FINISHED => 'onProjectFinished',
        ];
    }

    public function onProjectFinished(ProjectEvent $event): void
    {
        $this->createResearchCompletedLog($event);
    }

    private function createResearchCompletedLog(ProjectEvent $event): void
    {
        if (!$event->shouldPrintResearchCompletedLog() || !$event->hasAuthor()) {
            return;
        }

        $project = $event->getProject();
        $author = $event->getAuthor();

        $this->roomLogService->createLog(
            logKey: LogEnum::RESEARCH_COMPLETED,
            place: $author->getPlace(),
            visibility: VisibilityEnum::PUBLIC,
            type: 'event_log',
            player: $author,
            parameters: [
                $project->getLogKey() => $project->getLogName(),
            ]
        );
    }
}
