<?php

declare(strict_types=1);

namespace Mush\Project\Listener;

use Mush\Game\Event\VariableEventInterface;
use Mush\Project\Event\ProjectProgressEvent;
use Mush\Project\Repository\ProjectRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ProjectProgressEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ProjectRepositoryInterface $projectRepository,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            VariableEventInterface::CHANGE_VARIABLE => 'onChangeVariable',
        ];
    }

    public function onChangeVariable(VariableEventInterface $projectProgressEvent): void
    {
        if (!$projectProgressEvent instanceof ProjectProgressEvent) {
            return;
        }

        $project = $projectProgressEvent->getProject();
        $project->makeProgress($projectProgressEvent->getRoundedQuantity());

        $this->projectRepository->save($project);
    }
}