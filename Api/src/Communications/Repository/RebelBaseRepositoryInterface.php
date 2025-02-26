<?php

declare(strict_types=1);

namespace Mush\Communications\Repository;

use Mush\Communications\Entity\RebelBase;
use Mush\Communications\Enum\RebelBaseEnum;

interface RebelBaseRepositoryInterface
{
    public function deleteAllByDaedalusId(int $daedalusId): void;

    public function findAllByDaedalusId(int $daedalusId): array;

    public function findByDaedalusIdAndNameOrThrow(int $daedalusId, RebelBaseEnum $name): RebelBase;

    public function findMostRecentContactingRebelBase(int $daedalusId): ?RebelBase;

    public function findNextContactingRebelBase(int $daedalusId): ?RebelBase;

    public function hasNoContactingRebelBase(int $daedalusId): bool;

    public function save(RebelBase $rebelBase): void;
}
