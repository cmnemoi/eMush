<?php

declare(strict_types=1);

namespace Mush\Communications\Repository;

use Mush\Communications\Entity\RebelBase;
use Mush\Communications\Enum\RebelBaseEnum;

interface RebelBaseRepositoryInterface
{
    public function hasNoContactingRebelBase(int $daedalusId): bool;

    public function findByDaedalusIdAndNameOrThrow(int $daedalusId, RebelBaseEnum $name): RebelBase;

    public function save(RebelBase $rebelBase): void;
}
