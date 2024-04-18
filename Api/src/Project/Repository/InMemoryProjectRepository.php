<?php

declare(strict_types=1);

namespace Mush\Project\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Project\Entity\Project;

final class InMemoryProjectRepository implements ProjectRepositoryInterface
{
    private ArrayCollection $projects;

    public function __construct()
    {
        $this->projects = new ArrayCollection();
    }

    public function findByName(string $name): ?Project
    {
        return $this->projects->filter(static fn (Project $project) => $project->getName() === $name)->first() ?: null;
    }

    public function save(Project $project): void
    {
        $id = $this->projects->count();
        $this->projects[$id] = $project;
    }
}
