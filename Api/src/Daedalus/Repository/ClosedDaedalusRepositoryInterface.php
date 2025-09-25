<?php

declare(strict_types=1);

namespace Mush\Daedalus\Repository;

use Mush\Daedalus\Entity\ClosedDaedalus;

interface ClosedDaedalusRepositoryInterface
{
    public function save(ClosedDaedalus $closedDaedalus): void;

    public function findOneByIdOrThrow(int $id): ClosedDaedalus;
}
