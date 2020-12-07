<?php

namespace Mush\Equipment\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\EquipmentConfig;
use Mush\Equipment\Repository\EquipmentRepository;

class EquipmentService implements EquipmentServiceInterface
{
    private EquipmentRepository $equipmentRepository;

    /**
     * EquipmentService constructor.
     */
    public function __construct(EquipmentRepository $equipmentRepository)
    {
        $this->equipmentRepository = $equipmentRepository;
    }

    public function findByNameAndDaedalus(string $name, Daedalus $daedalus): EquipmentConfig
    {
        return $this->equipmentRepository->findByNameAndDaedalus($name, $daedalus);
    }
}
