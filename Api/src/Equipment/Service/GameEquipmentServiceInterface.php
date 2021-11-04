<?php

namespace Mush\Equipment\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\EquipmentHolderInterface;
use Mush\Equipment\Entity\GameEquipment;

interface GameEquipmentServiceInterface
{
    public function persist(GameEquipment $equipment): GameEquipment;

    public function delete(GameEquipment $equipment): void;

    public function findByNameAndDaedalus(string $name, Daedalus $daedalus): ArrayCollection;

    public function findById(int $id): ?GameEquipment;

    public function createGameEquipmentFromName(
        string $equipmentName,
        EquipmentHolderInterface $equipmentHolder,
        string $reason,
        \DateTime $time
    ): GameEquipment;

    public function createGameEquipment(
        EquipmentConfig $equipmentConfig,
        EquipmentHolderInterface $holder,
        string $reason,
        \DateTime $time
    ): GameEquipment;

    public function handleBreakFire(GameEquipment $gameEquipment, \DateTime $date): void;
}
