<?php

declare(strict_types=1);

namespace Mush\Project\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Order;
use Mush\Project\Entity\Project;

/**
 * @template-extends ArrayCollection<int, Project>
 */
final class ProjectCollection extends ArrayCollection
{
    public function getLastAdvancedProject(): Project
    {
        return $this->matching(
            Criteria::create()->orderBy(['lastParticipationTime' => Order::Descending])
        )->first() ?: Project::createNull();
    }

    public function getLastAdvancedProjectOrThrow(): Project
    {
        $project = $this->getLastAdvancedProject();
        if ($project->isNull()) {
            throw new \RuntimeException('No last advanced project found');
        }

        return $project;
    }

    public function getFinishedProjects(): self
    {
        return $this->filter(static fn (Project $project) => $project->isFinished());
    }

    public function getAllProjectsExcept(Project $projectToExclude): self
    {
        return $this->filter(static fn (Project $project) => $project->notEquals($projectToExclude));
    }

    public function getAdvancedNeronProjects(): self
    {
        return $this
            ->filter(static fn (Project $project) => $project->isProposedNeronProject())
            ->filter(static fn (Project $project) => $project->hasBeenAdvanced());
    }
}
