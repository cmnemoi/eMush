<?php

declare(strict_types=1);

namespace Mush\Communication\Listener;

use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Communication\Services\NeronMessageServiceInterface;
use Mush\Project\Event\ProjectEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ProjectEventSubscriber implements EventSubscriberInterface
{
    private NeronMessageServiceInterface $neronMessageService;

    public function __construct(NeronMessageServiceInterface $neronMessageService)
    {
        $this->neronMessageService = $neronMessageService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProjectEvent::PROJECT_FINISHED => 'onProjectFinished',
        ];
    }

    public function onProjectFinished(ProjectEvent $event): void
    {
        $author = $event->getAuthor();
        $project = $event->getProject();

        $this->neronMessageService->createNeronMessage(
            messageKey: $project->isPilgred() ? NeronMessageEnum::REPAIRED_PILGRED : NeronMessageEnum::NEW_PROJECT,
            daedalus: $event->getDaedalus(),
            parameters: [
                $author->getLogKey() => $author->getLogName(),
                $project->getLogKey() => $project->getLogName(),
            ],
            dateTime: $event->getTime(),
        );
    }
}
