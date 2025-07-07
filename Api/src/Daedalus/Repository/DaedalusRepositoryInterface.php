<?php

declare(strict_types=1);

namespace Mush\Daedalus\Repository;

use Doctrine\DBAL\LockMode;
use Mush\Daedalus\Entity\Daedalus;

interface DaedalusRepositoryInterface
{
    public function clear(): void;

    public function lockAndRefresh(Daedalus $daedalus, int $mode = LockMode::PESSIMISTIC_WRITE): Daedalus;

    public function findByIdOrThrow(int $id): Daedalus;

    public function save(Daedalus $daedalus): void;
}
