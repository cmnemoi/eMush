<?php

namespace Mush\Equipment\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\EquipmentHolderInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Enum\VisibilityEnum;

interface GameEquipmentServiceInterface
{
    public function persist(GameEquipment $equipment): GameEquipment;

    public function delete(GameEquipment $equipment): void;

    public function findByNameAndDaedalus(string $name, Daedalus $daedalus): ArrayCollection;

    public function findById(int $id): ?GameEquipment;

    public function createGameEquipmentFromName(
        string $equipmentName,
        EquipmentHolderInterface $equipmentHolder,
        array $reasons,
        \DateTime $time,
        string $visibility = VisibilityEnum::PRIVATE
    ): GameEquipment;

    public function createGameEquipment(
        EquipmentConfig $equipmentConfig,
        EquipmentHolderInterface $holder,
        array $reasons,
        \DateTime $time,
        string $visibility = VisibilityEnum::HIDDEN
    ): GameEquipment;

    public function transformGameEquipmentToEquipmentWithName(
        string $resultName,
        GameEquipment $input,
        EquipmentHolderInterface $holder,
        array $reasons,
        \DateTime $time,
        string $visibility = VisibilityEnum::HIDDEN
    ): GameEquipment;

    public function transformGameEquipmentToEquipment(
        EquipmentConfig $resultConfig,
        GameEquipment $input,
        EquipmentHolderInterface $holder,
        array $reasons,
        \DateTime $time,
        string $visibility = VisibilityEnum::HIDDEN
    ): GameEquipment;

    public function handleBreakFire(GameEquipment $gameEquipment, \DateTime $date): void;
}
