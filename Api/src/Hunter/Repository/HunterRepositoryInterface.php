<?php

declare(strict_types=1);

namespace Mush\Hunter\Repository;

use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Entity\HunterTarget;

interface HunterRepositoryInterface
{
    public function findByIdOrThrow(int $id): Hunter;

    public function findOneByTargetOrThrow(HunterTarget $hunterTarget): Hunter;

    public function save(Hunter $hunter): void;
}
