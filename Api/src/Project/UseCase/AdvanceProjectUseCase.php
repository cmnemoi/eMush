<?php

declare(strict_types=1);

namespace Mush\Project\UseCase;

use Mush\Game\Service\Random\GetRandomIntegerServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Project\Entity\Project;
use Mush\Project\Repository\ProjectRepositoryInterface;

final class AdvanceProjectUseCase
{
    public function __construct(
        private ProjectRepositoryInterface $projectRepository,
        private GetRandomIntegerServiceInterface $getRandomIntegerService,
    ) {}

    public function execute(Player $player, Project $project): void
    {
        $efficiency = $player->getEfficiencyForProject($project);
        $progress = $this->getRandomIntegerService->execute($efficiency->min, $efficiency->max);

        $project->makeProgressAndUpdateParticipationDate($progress);
        $project->addPlayerParticipation($player);

        $this->projectRepository->save($project);
    }
}
