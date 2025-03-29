<?php

declare(strict_types=1);

namespace Mush\Project\Service;

use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Project\Entity\Project;
use Mush\Project\Event\ProjectEvent;
use Mush\Project\Repository\ProjectRepositoryInterface;

final readonly class FinishProjectService
{
    public function __construct(
        private EventServiceInterface $eventService,
        private ProjectRepositoryInterface $projectRepository
    ) {}

    public function execute(Project $project): void
    {
        $project->finish();
        $this->projectRepository->save($project);

        $this->eventService->callEvent(
            event: new ProjectEvent($project, author: Player::createNull()),
            name: ProjectEvent::PROJECT_FINISHED
        );
    }
}
