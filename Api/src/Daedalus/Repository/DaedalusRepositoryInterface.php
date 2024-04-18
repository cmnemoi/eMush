<?php

declare(strict_types=1);

namespace Mush\Daedalus\Repository;

use Mush\Daedalus\Entity\Daedalus;

interface DaedalusRepositoryInterface
{
    public function clear(): void;

    public function findByName(string $name): Daedalus;

    public function save(Daedalus $daedalus): void;
}
