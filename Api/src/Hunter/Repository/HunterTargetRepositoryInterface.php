<?php

declare(strict_types=1);

namespace Mush\Hunter\Repository;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Hunter\Entity\HunterTarget;

interface HunterTargetRepositoryInterface
{
    public function delete(HunterTarget $hunterTarget): void;

    public function save(HunterTarget $hunterTarget): void;

    /** @return HunterTarget[] */
    public function findAllBy(array $criteria): array;

    /** @return HunterTarget[] */
    public function findAllByPatrolShip(GameEquipment $patrolShip): array;
}
