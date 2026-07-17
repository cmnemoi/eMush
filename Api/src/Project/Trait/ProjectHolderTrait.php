<?php

declare(strict_types=1);

namespace Mush\Project\Trait;

use Doctrine\Common\Collections\Collection;
use Mush\Communications\Collection\RebelBaseCollection;
use Mush\Player\Entity\Player;
use Mush\Project\Collection\ProjectCollection;
use Mush\Project\Entity\Project;
use Mush\Project\Entity\ProjectHolderInterface;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Exception\DaedalusShouldHaveProjectException;

/**
 * Trait for entities manipulating a collection of `Project`.
 *
 * The using entity should still implement the `getProjects()` method.
 *
 * @mixin ProjectHolderInterface
 *
 * @property Collection $projects
 */
trait ProjectHolderTrait
{
    public function addProject(Project $project): static
    {
        if (!$this->projects->contains($project)) {
            $this->projects->add($project);
        }

        return $this;
    }

    public function getProjectByName(ProjectName $projectName): Project
    {
        $project = $this->getProjects()->filter(static fn (Project $project) => $project->getName() === $projectName->value)->first();
        if (!$project) {
            throw new DaedalusShouldHaveProjectException($projectName);
        }

        return $project;
    }

    public function hasProject(ProjectName $projectName): bool
    {
        return $this->getProjects()->exists(static fn ($key, Project $project) => $project->getName() === $projectName->value);
    }

    public function hasFinishedProject(ProjectName $projectName): bool
    {
        return $this->getProjectByName($projectName)->isFinished();
    }

    public function getAllAvailableProjects(): ProjectCollection
    {
        return $this->getProjects()->filter(static fn (Project $project) => $project->isAvailable());
    }

    public function getResearchProjects(): ProjectCollection
    {
        return $this->getProjects()->filter(static fn (Project $project) => $project->isResearchProject());
    }

    public function getFinishedResearchProjects(): ProjectCollection
    {
        return $this->getResearchProjects()->filter(static fn (Project $project) => $project->isFinished());
    }

    public function getVisibleResearchProjectsForPlayer(Player $player, RebelBaseCollection $rebelBases): ProjectCollection
    {
        return $this
            ->getResearchProjects()
            ->filter(static fn (Project $project) => $project->isNotFinished())
            ->filter(static fn (Project $project) => $project->isVisibleFor($player, $rebelBases));
    }

    public function getAdvancedResearchProjects(): ProjectCollection
    {
        return $this->getProjects()->getAdvancedResearchProjects();
    }

    public function getAvailableNeronProjects(): ProjectCollection
    {
        return $this->getProjects()->filter(static fn (Project $project) => $project->isAvailableNeronProject());
    }

    public function getProposedNeronProjects(): ProjectCollection
    {
        return $this->getProjects()->filter(static fn (Project $project) => $project->isProposedNeronProject());
    }

    public function hasProposedNeronProjects(): bool
    {
        return $this->getProposedNeronProjects()->count() > 0;
    }

    public function getFinishedNeronProjects(): ProjectCollection
    {
        return $this->getProjects()->filter(static fn (Project $project) => $project->isFinishedNeronProject());
    }

    public function getAdvancedNeronProjects(): ProjectCollection
    {
        return $this->getProjects()->getAdvancedNeronProjects();
    }

    public function getAllFinishedProjects(): ProjectCollection
    {
        return $this->getProjects()->filter(static fn (Project $project) => $project->isFinished());
    }
}
