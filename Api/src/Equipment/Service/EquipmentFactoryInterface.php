<?php

namespace Mush\Equipment\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\EquipmentHolderInterface;
use Mush\Equipment\Entity\Equipment;
use Mush\Place\Entity\Place;

interface EquipmentFactoryInterface
{

    public function createDoor(string $name, Place $place, EquipmentConfig $config);

    public function deleteEquipment(Equipment $equipment, string $reason, string $visibility): void;

    public function findByNameAndDaedalus(string $name, Daedalus $daedalus): ArrayCollection;

    public function createGameEquipmentFromName(
        string $name,
        EquipmentHolderInterface $holder,
        string $visibility,
        string $reason
    ): Equipment;

    public function createGameEquipment(
        EquipmentConfig $config,
        EquipmentHolderInterface $holder,
        string $visibility,
        string $reason
    ): Equipment;

    public function handleBreakFire(Equipment $gameEquipment, \DateTime $date): void;
}
