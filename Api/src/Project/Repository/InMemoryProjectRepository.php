<?php

declare(strict_types=1);

namespace Mush\Project\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Project\Entity\Project;
use Mush\Project\Enum\ProjectName;

final class InMemoryProjectRepository implements ProjectRepositoryInterface
{
    private ArrayCollection $projects;

    public function __construct()
    {
        $this->projects = new ArrayCollection();
    }

    public function findByName(ProjectName $name): ?Project
    {
        return $this->projects->filter(static fn (Project $project) => $project->getName() === $name)->first() ?: null;
    }

    public function save(Project $project): void
    {
        $id = $this->projects->count();
        $this->projects[$id] = $project;
    }
}
