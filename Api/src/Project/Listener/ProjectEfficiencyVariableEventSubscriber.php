<?php

declare(strict_types=1);

namespace Mush\Project\Listener;

use Mush\Game\Event\VariableEventInterface;
use Mush\Project\Event\ProjectEfficiencyVariableEvent;
use Mush\Project\Repository\ProjectRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ProjectEfficiencyVariableEventSubscriber implements EventSubscriberInterface
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

    public function onChangeVariable(VariableEventInterface $projectEvent): void
    {
        if (!$projectEvent instanceof ProjectEfficiencyVariableEvent) {
            return;
        }

        $project = $projectEvent->getProject();
        $project->updateEfficiency($projectEvent->getRoundedQuantity());

        $this->projectRepository->save($project);
    }
}
