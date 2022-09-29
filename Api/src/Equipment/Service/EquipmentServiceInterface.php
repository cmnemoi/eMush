<?php

namespace Mush\Equipment\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Equipment;

interface EquipmentServiceInterface
{
    public function findById(int $id): ?Equipment;

    public function findByNameAndDaedalus(string $name, Daedalus $daedalus): EquipmentConfig;

    public function persist(Equipment $equipment): Equipment;

    public function delete(Equipment $equipment): void;
}
