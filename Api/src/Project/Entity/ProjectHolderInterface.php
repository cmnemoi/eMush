<?php

declare(strict_types=1);

namespace Mush\Project\Entity;

use Doctrine\Common\Collections\Collection;
use Mush\Communications\Collection\RebelBaseCollection;
use Mush\Player\Entity\Player;
use Mush\Project\Collection\ProjectCollection;
use Mush\Project\Enum\ProjectName;

/**
 * Interface for entities manipulating a collection of `Project`.
 */
interface ProjectHolderInterface
{
    public function getProjects(): ProjectCollection;

    public function addProject(Project $project): static;

    public function getProjectByName(ProjectName $projectName): Project;

    public function hasProject(ProjectName $projectName): bool;

    public function hasFinishedProject(ProjectName $projectName): bool;

    public function getAllAvailableProjects(): ProjectCollection;

    // Research projects
    public function getResearchProjects(): ProjectCollection;

    public function getFinishedResearchProjects(): ProjectCollection;

    public function getVisibleResearchProjectsForPlayer(Player $player, RebelBaseCollection $rebelBases): ProjectCollection;

    public function getAdvancedResearchProjects(): ProjectCollection;

    // Neron projects
    public function getAvailableNeronProjects(): ProjectCollection;

    public function getProposedNeronProjects(): ProjectCollection;

    public function hasProposedNeronProjects(): bool;

    public function getFinishedNeronProjects(): ProjectCollection;

    public function getAdvancedNeronProjects(): ProjectCollection;

    public function getAllFinishedProjects(): ProjectCollection;
}
