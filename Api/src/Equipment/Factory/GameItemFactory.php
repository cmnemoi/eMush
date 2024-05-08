<?php

declare(strict_types=1);

namespace Mush\Equipment\Factory;

use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Place\Entity\Place;

final class GameItemFactory
{
    public static function createEquipmentByName(string $name): GameItem
    {
        $holder = new Place();

        $itemConfig = new EquipmentConfig();
        $itemConfig
            ->setEquipmentName($name)
            ->buildName(GameConfigEnum::DEFAULT);

        $item = new GameItem($holder);
        $item
            ->setEquipment($itemConfig)
            ->setName($itemConfig->getEquipmentName());

        return $item;
    }

    public static function createBlockOfPostIt(): GameItem
    {
        $holder = new Place();

        $itemConfig = new EquipmentConfig();
        $itemConfig
            ->setEquipmentName(ToolItemEnum::BLOCK_OF_POST_IT)
            ->buildName(GameConfigEnum::DEFAULT);

        $item = new GameItem($holder);
        $item
            ->setEquipment($itemConfig)
            ->setName($itemConfig->getEquipmentName());

        return $item;
    }
}
