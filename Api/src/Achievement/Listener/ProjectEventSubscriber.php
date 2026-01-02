<?php

declare(strict_types=1);

namespace Mush\Achievement\Listener;

use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Services\UpdatePlayerStatisticService;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Enum\ProjectType;
use Mush\Project\Event\ProjectEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ProjectEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private UpdatePlayerStatisticService $updatePlayerStatisticService) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ProjectEvent::PROJECT_FINISHED => ['onProjectFinished', EventPriorityEnum::LOWEST],
        ];
    }

    public function onProjectFinished(ProjectEvent $event): void
    {
        $this->advanceForSpecificProjectFinished($event);
        $this->advanceForAuthorOfFinishedProjectType($event);
        $this->advanceForCrewWhenFinishedProjectType($event);
    }

    private function advanceForSpecificProjectFinished(ProjectEvent $event): void
    {
        $statisticName = match ($event->getProject()->getName()) {
            ProjectName::PILGRED->toString() => StatisticEnum::PILGRED_IS_BACK,
            ProjectName::PLASMA_SHIELD->toString() => StatisticEnum::PLASMA_SHIELD,
            default => StatisticEnum::NULL,
        };

        foreach ($event->getDaedalus()->getAlivePlayers() as $player) {
            $this->updatePlayerStatisticService->execute(
                player: $player,
                statisticName: $statisticName,
            );
        }
    }

    private function advanceForAuthorOfFinishedProjectType(ProjectEvent $event): void
    {
        if (!$event->hasAuthor()) {
            return;
        }

        $statisticName = match ($event->getProject()->getType()) {
            ProjectType::NERON_PROJECT => StatisticEnum::PROJECT_COMPLETE,
            ProjectType::RESEARCH => StatisticEnum::RESEARCH_COMPLETE,
            default => StatisticEnum::NULL,
        };

        $this->updatePlayerStatisticService->execute(
            player: $event->getAuthor(),
            statisticName: $statisticName,
        );
    }

    private function advanceForCrewWhenFinishedProjectType(ProjectEvent $event): void
    {
        $statisticName = match ($event->getProject()->getType()) {
            ProjectType::NERON_PROJECT => StatisticEnum::PROJECT_TEAM,
            ProjectType::RESEARCH => StatisticEnum::RESEARCH_TEAM,
            default => StatisticEnum::NULL,
        };

        $numberOfFinishedProjects = match ($event->getProject()->getType()) {
            ProjectType::NERON_PROJECT => $event->getDaedalus()->getFinishedNeronProjects()->count(),
            ProjectType::RESEARCH => $event->getDaedalus()->getFinishedResearchProjects()->count(),
            default => 0,
        };

        foreach ($event->getDaedalus()->getAlivePlayers() as $player) {
            $this->updatePlayerStatisticService->execute(
                player: $player,
                statisticName: $statisticName,
                count: $numberOfFinishedProjects,
            );
        }
    }
}
