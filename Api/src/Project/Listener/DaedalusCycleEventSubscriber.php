<?php

declare(strict_types=1);

namespace Mush\Project\Listener;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Game\Enum\EventPriorityEnum;
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
        foreach ($daedalus->getProposedNeronProjects() as $project) {
            $project->makeProgress(self::NERON_PROJECT_THREAD_PROGRESS);
            $this->projectRepository->save($project);
        }
    }
}
