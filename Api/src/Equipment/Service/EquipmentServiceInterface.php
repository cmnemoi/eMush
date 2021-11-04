<?php

namespace Mush\Equipment\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;

interface EquipmentServiceInterface
{
    public function findByNameAndDaedalus(string $name, Daedalus $daedalus): EquipmentConfig;
}
