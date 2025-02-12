<?php

namespace Mush\Communications\Repository;

use Mush\Communications\Entity\NeronVersion;

interface NeronVersionRepositoryInterface
{
    public function findByDaedalusIdOrThrow(int $daedalusId): NeronVersion;

    public function save(NeronVersion $neronVersion): void;
}
