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
            ->filter(fn (EquipmentConfig $item) => $item->getName() === $name)
        ;

        if ($items->count() !== 1) {
            throw new \Error('there should be exactly one equipmentConfig with this name');
        }

        return $items->first();
    }
}
