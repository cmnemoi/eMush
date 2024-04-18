<?php

declare(strict_types=1);

namespace Mush\Project\Repository;

use Mush\Project\Entity\Project;
use Mush\Project\Enum\ProjectName;

interface ProjectRepositoryInterface
{
    public function findByName(ProjectName $name): ?Project;

    public function save(Project $project): void;
}
