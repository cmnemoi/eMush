<?php

declare(strict_types=1);

namespace Mush\Equipment\Factory;

use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Place\Entity\Place;

final class GameEquipmentFactory
{
    public static function createEquipmentByName(string $name = EquipmentEnum::PILGRED): GameEquipment
    {
        $holder = new Place();

        $equipmentConfig = new EquipmentConfig();
        $equipmentConfig
            ->setEquipmentName($name)
            ->buildName(GameConfigEnum::DEFAULT);

        $equipment = new GameEquipment($holder);
        $equipment
            ->setEquipment($equipmentConfig)
            ->setName($equipmentConfig->getEquipmentName());

        return $equipment;
    }

    public static function createPilgredEquipment(): GameEquipment
    {
        $holder = new Place();

        $equipmentConfig = new EquipmentConfig();
        $equipmentConfig
            ->setEquipmentName(EquipmentEnum::PILGRED)
            ->buildName(GameConfigEnum::DEFAULT);

        $equipment = new GameEquipment($holder);
        $equipment
            ->setEquipment($equipmentConfig)
            ->setName($equipmentConfig->getEquipmentName());

        return $equipment;
    }
}
