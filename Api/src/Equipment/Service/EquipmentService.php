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
            ->filter(fn (EquipmentConfig $item) => $item->getEquipmentName() === $name)
        ;

        if ($items->count() !== 1) {
            throw new \Error("There should be exactly 1 equipmentConfig with this name ({$name}). There are currently {$items->count()}");
        }

        return $items->first();
    }
}
