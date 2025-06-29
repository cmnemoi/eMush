<?php

declare(strict_types=1);

namespace Mush\Project\Repository;

use Doctrine\DBAL\LockMode;
use Mush\Project\Entity\Project;

interface ProjectRepositoryInterface
{
    public function clear(): void;

    public function findByName(string $name): ?Project;

    public function lockAndRefresh(Project $project, int $mode = LockMode::PESSIMISTIC_WRITE): Project;

    public function save(Project $project): void;
}
