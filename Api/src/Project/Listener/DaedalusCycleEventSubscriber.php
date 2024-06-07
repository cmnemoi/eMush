<?php

declare(strict_types=1);

namespace Mush\Project\Listener;

use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Project\Collection\ProjectCollection;
use Mush\Project\Entity\Project;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Event\ProjectEvent;
use Mush\Project\Repository\ProjectRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class DaedalusCycleEventSubscriber implements EventSubscriberInterface
{
    private const int NERON_PROJECT_THREAD_PROGRESS = 5;

    public function __construct(
        private EventServiceInterface $eventService,
        private ProjectRepositoryInterface $projectRepository
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            DaedalusCycleEvent::DAEDALUS_NEW_CYCLE => ['onDaedalusNewCycle', EventPriorityEnum::NERON_PROJECT_THREAD],
        ];
    }

    public function onDaedalusNewCycle(DaedalusCycleEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        if ($daedalus->projectIsNotFinished(ProjectName::NERON_PROJECT_THREAD)) {
            return;
        }

        $this->makeProposedNeronProjectsProgress($event);
    }

    private function makeProposedNeronProjectsProgress(DaedalusCycleEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $proposedNeronProjects = $daedalus->getProposedNeronProjects();

        $this->makeProjectsProgress($proposedNeronProjects);
        $this->finishOnlyLastAdvancedProjectFrom($proposedNeronProjects);
        $this->dispatchProjectAdvancedEventForAllProjects($proposedNeronProjects, $event);
        $this->saveProjectsInRepository($proposedNeronProjects);
    }

    private function makeProjectsProgress(ProjectCollection $proposedNeronProjects): void
    {
        foreach ($proposedNeronProjects as $project) {
            $project->makeProgress(self::NERON_PROJECT_THREAD_PROGRESS);
        }
    }

    private function finishOnlyLastAdvancedProjectFrom(ProjectCollection $proposedNeronProjects): void
    {
        // If there is at most one project at 100% progress, we don't need to do anything
        $projectsAt100Percents = $proposedNeronProjects->getFinishedProjects();
        if ($projectsAt100Percents->count() <= 1) {
            return;
        }

        // else, we need to revert progress for all projects except the last advanced one by a player
        $lastAdvancedProject = $projectsAt100Percents->getLastAdvancedProjectOrThrow();
        $projectsToDrop = $projectsAt100Percents->getAllProjectsExcept($lastAdvancedProject);

        foreach ($projectsToDrop as $project) {
            $project->revertProgress(self::NERON_PROJECT_THREAD_PROGRESS);
        }
    }

    private function dispatchProjectAdvancedEventForAllProjects(ProjectCollection $proposedNeronProjects, DaedalusCycleEvent $event): void
    {
        foreach ($proposedNeronProjects as $project) {
            $projectEvent = new ProjectEvent(
                project: $project,
                author: Player::createNull(),
                tags: $event->getTags(),
                time: $event->getTime(),
            );
            $this->eventService->callEvent($projectEvent, ProjectEvent::PROJECT_ADVANCED);
        }
    }

    private function saveProjectsInRepository(ProjectCollection $proposedNeronProjects): void
    {
        foreach ($proposedNeronProjects as $project) {
            $this->projectRepository->save($project);
        }
    }
}
