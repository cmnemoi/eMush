<?php

declare(strict_types=1);

namespace Mush\Project\Listener;

use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Event\ProjectEvent;
use Mush\Project\Repository\ProjectRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class DaedalusCycleEventSubscriber implements EventSubscriberInterface
{
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

        $this->applyNeronProjectThread($event);
    }

    private function applyNeronProjectThread(DaedalusCycleEvent $event): void
    {
        $this->makeProjectsProgress($event);
        $this->finishOnlyLastAdvancedProject($event);
        $this->dispatchProjectAdvancedEventForAllProjects($event);
        $this->saveProjectsInRepository($event);
    }

    private function makeProjectsProgress(DaedalusCycleEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $neronProjectThreadBonus = $daedalus->getProjectByName(ProjectName::NERON_PROJECT_THREAD)->getActivationRate();

        $proposedNeronProjects = $daedalus->getProposedNeronProjects();

        foreach ($proposedNeronProjects as $project) {
            $project->makeProgress($neronProjectThreadBonus);
        }
    }

    private function finishOnlyLastAdvancedProject(DaedalusCycleEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $proposedNeronProjects = $daedalus->getProposedNeronProjects();

        // If there is at most one project at 100% progress, we don't need to do anything
        $projectsAt100Percents = $proposedNeronProjects->getFinishedProjects();
        if ($projectsAt100Percents->count() <= 1) {
            return;
        }

        // else, we need to revert progress for all projects except the last advanced one by a player
        $lastAdvancedProject = $projectsAt100Percents->getLastAdvancedProjectOrThrow();
        $projectsToDrop = $projectsAt100Percents->getAllProjectsExcept($lastAdvancedProject);

        $neronProjectThreadBonus = $daedalus->getProjectByName(ProjectName::NERON_PROJECT_THREAD)->getActivationRate();
        foreach ($projectsToDrop as $project) {
            $project->revertProgress($neronProjectThreadBonus);
        }
    }

    private function dispatchProjectAdvancedEventForAllProjects(DaedalusCycleEvent $event): void
    {
        $proposedNeronProjects = $event->getDaedalus()->getProposedNeronProjects();

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

    private function saveProjectsInRepository(DaedalusCycleEvent $event): void
    {
        $proposedNeronProjects = $event->getDaedalus()->getProposedNeronProjects();

        foreach ($proposedNeronProjects as $project) {
            $this->projectRepository->save($project);
        }
    }
}
