<?php

declare(strict_types=1);

namespace Mush\Project\Repository;

use Mush\Project\Entity\Project;

interface ProjectRepositoryInterface
{
    public function findByName(string $name): ?Project;

    public function save(Project $project): void;
}
