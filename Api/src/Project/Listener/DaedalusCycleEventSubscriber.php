<?php

declare(strict_types=1);

namespace Mush\Project\Listener;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Order;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Project\Entity\Project;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Repository\ProjectRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class DaedalusCycleEventSubscriber implements EventSubscriberInterface
{
    private const int NERON_PROJECT_THREAD_PROGRESS = 5;

    public function __construct(private ProjectRepositoryInterface $projectRepository) {}

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

        $this->makeProposedNeronProjectsProgress($daedalus);
    }

    private function makeProposedNeronProjectsProgress(Daedalus $daedalus): void
    {
        $proposedNeronProjects = $daedalus->getProposedNeronProjects();
        foreach ($proposedNeronProjects as $project) {
            $project->makeProgress(self::NERON_PROJECT_THREAD_PROGRESS);
            $this->projectRepository->save($project);
        }

        $projectsAt100Percents = $proposedNeronProjects->filter(static fn (Project $project) => $project->isFinished());
        if ($projectsAt100Percents->count() > 1) {
            /** @var Project $oldestAdvancedProject */
            $oldestAdvancedProject = $projectsAt100Percents->matching(Criteria::create()->orderBy(['lastParticipationTime' => Order::Descending]))->first();
            $oldestAdvancedProject->revertProgress(self::NERON_PROJECT_THREAD_PROGRESS);
            $this->projectRepository->save($oldestAdvancedProject);
        }
    }
}
