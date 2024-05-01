<?php

declare(strict_types=1);

namespace Mush\Equipment\Factory;

use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\EquipmentHolderInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Place\Entity\Place;

final class GameEquipmentFactory
{
    public static function createEquipmentByName(string $name): GameEquipment
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

    public static function createEquipmentByNameForHolder(string $name, EquipmentHolderInterface $holder): GameEquipment
    {
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

    public static function createItemByNameForHolder(string $name, EquipmentHolderInterface $holder): GameItem
    {
        $itemConfig = new ItemConfig();
        $itemConfig
            ->setEquipmentName($name)
            ->buildName(GameConfigEnum::DEFAULT);

        $item = new GameItem($holder);
        $item
            ->setEquipment($itemConfig)
            ->setName($itemConfig->getEquipmentName());

        return $item;
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
