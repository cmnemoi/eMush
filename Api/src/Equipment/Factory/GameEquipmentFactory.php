<?php

declare(strict_types=1);

namespace Mush\Equipment\Factory;

use Mush\Equipment\Entity\Config\DroneConfig;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\Config\SpaceShipConfig;
use Mush\Equipment\Entity\Drone;
use Mush\Equipment\Entity\DroneInfo;
use Mush\Equipment\Entity\EquipmentHolderInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\SpaceShip;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Place\Entity\Place;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Factory\StatusFactory;

final class GameEquipmentFactory
{
    public static function createDroneForHolder(EquipmentHolderInterface $holder): Drone
    {
        $droneConfig = new DroneConfig();
        $droneConfig
            ->setEquipmentName(ItemEnum::SUPPORT_DRONE)
            ->buildName(GameConfigEnum::DEFAULT);

        $drone = $droneConfig->createGameEquipment($holder);

        self::setupEquipmentId($drone);

        StatusFactory::createChargeStatusFromStatusName(
            EquipmentStatusEnum::ELECTRIC_CHARGES,
            $drone,
        );

        $drone->setDroneInfo(new DroneInfo($drone, nickName: 0, serialNumber: 0));

        return $drone;
    }

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
        if (!$holder instanceof Place) {
            throw new \LogicException('Only Place holders are supported! If you want to create an equipment for a player, use createItemByNameForHolder instead.');
        }

        $equipmentConfig = new EquipmentConfig();
        $equipmentConfig
            ->setEquipmentName($name)
            ->buildName(GameConfigEnum::DEFAULT);

        $equipment = new GameEquipment($holder);
        $equipment
            ->setEquipment($equipmentConfig)
            ->setName($equipmentConfig->getEquipmentName());

        self::setupEquipmentId($equipment);

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

    public static function createPatrolShipByNameForHolder(string $name, EquipmentHolderInterface $holder): SpaceShip
    {
        $patrolShipConfig = new SpaceShipConfig();
        $patrolShipConfig
            ->setEquipmentName($name)
            ->buildName(GameConfigEnum::DEFAULT);

        $patrolShip = $patrolShipConfig->createGameEquipment($holder);
        $patrolShip->setPatrolShipName(EquipmentEnum::PATROL_SHIP_ALPHA_TAMARIN);

        self::setupEquipmentId($patrolShip);

        return $patrolShip;
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

    private static function setupEquipmentId(GameEquipment $equipment): void
    {
        (new \ReflectionProperty($equipment, 'id'))->setValue($equipment, crc32(serialize($equipment)));
    }
}
