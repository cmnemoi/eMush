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
            ->getEquipmentConfigs()
            ->filter(fn (EquipmentConfig $item) => $item->getEquipmentName() === $name)
        ;

        if ($items->count() !== 1) {
            throw new \Error('there should be exactly one equipmentConfig with this name');
        }

        return $items->first();
    }
}
