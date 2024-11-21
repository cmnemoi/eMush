<?php

declare(strict_types=1);

namespace Mush\Hunter\Repository;

use Mush\Hunter\Entity\HunterTarget;

interface HunterTargetRepositoryInterface
{
    public function delete(HunterTarget $hunterTarget): void;

    /** @return HunterTarget[] */
    public function findAllBy(array $criteria): array;
}
