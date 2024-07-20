<?php

declare(strict_types=1);

namespace Mush\Equipment\Repository;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;

interface GameEquipmentRepositoryInterface
{
    public function findByDaedalus(Daedalus $daedalus): array;

    public function save(GameEquipment $gameEquipment): void;
}
