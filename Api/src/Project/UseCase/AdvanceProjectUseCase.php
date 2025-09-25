<?php

declare(strict_types=1);

namespace Mush\Project\UseCase;

use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\GetRandomIntegerServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Project\Entity\Project;
use Mush\Project\Event\ProjectEvent;
use Mush\Project\Repository\ProjectRepositoryInterface;

final class AdvanceProjectUseCase
{
    public function __construct(
        private EventServiceInterface $eventService,
        private GetRandomIntegerServiceInterface $getRandomInteger,
        private ProjectRepositoryInterface $projectRepository,
    ) {}

    public function execute(Player $player, Project $project, array $tags): void
    {
        $efficiency = $player->getEfficiencyForProject($project);
        $progress = $this->getRandomInteger->execute($efficiency->min, $efficiency->max);

        $project->makeProgressAndUpdateParticipationDate($progress);
        $project->addPlayerParticipation($player);

        $this->projectRepository->save($project);

        $projectEvent = new ProjectEvent($project, $player, $tags);
        $this->eventService->callEvent($projectEvent, ProjectEvent::PROJECT_ADVANCED);
    }
}
