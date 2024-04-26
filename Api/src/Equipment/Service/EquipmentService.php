<?php

namespace Mush\Equipment\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;

class EquipmentService implements EquipmentServiceInterface
{
    public function findByNameAndDaedalus(string $name, Daedalus $daedalus): EquipmentConfig
    {
        $items = $daedalus
            ->getGameConfig()
            ->getEquipmentsConfig()
            ->filter(static fn (EquipmentConfig $item) => $item->getEquipmentName() === $name);

        if ($items->count() !== 1) {
            throw new \Exception("there should be exactly one equipmentConfig with this name {$name}, found {$items->count()}");
        }

        return $items->first();
    }
}
