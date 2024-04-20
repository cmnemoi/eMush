<?php

declare(strict_types=1);

namespace Mush\Project\UseCase;

use Mush\Game\Service\GetRandomIntegerServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Project\Entity\Project;
use Mush\Project\Repository\ProjectRepositoryInterface;

final class AdvanceProjectUseCase
{
    public function __construct(
        private ProjectRepositoryInterface $projectRepositoryInterface,
        private GetRandomIntegerServiceInterface $getRandomIntegerService,
    ) {}

    public function execute(Player $player, Project $project): void
    {
        $minEfficiency = $player->getMinEfficiencyForProject($project);
        $maxEfficiency = $player->getMaxEfficiencyForProject($project);

        $progress = $this->getRandomIntegerService->execute($minEfficiency, $maxEfficiency);
        $project->makeProgress($progress);

        $this->projectRepositoryInterface->save($project);
    }
}
