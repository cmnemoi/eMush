<?php

declare(strict_types=1);

namespace Mush\Equipment\Repository;

use Mush\Daedalus\Entity\Daedalus;

interface GameEquipmentRepositoryInterface
{
    public function findByDaedalus(Daedalus $daedalus): array;
}
