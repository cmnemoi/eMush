<?php

namespace Mush\Item\Service;

use Mush\Item\Entity\Collection\ItemConfigCollection;
use Mush\Item\Entity\ItemConfig;

class ItemConfigService implements ItemConfigServiceInterface
{
    public function getConfigs(): ItemConfigCollection
    {
        $itemsConfig = \ITEM_CONFIG;
        $configs = new ItemConfigCollection();

        foreach ($itemsConfig as $itemConfig) {
            $config = new ItemConfig();
            $config
                ->setName($itemConfig['name'])
                ->setType($itemConfig['type'])
                ->setIsHeavy($itemConfig['isHeavy'])
                ->setIsDismantable($itemConfig['isDismantable'])
                ->setIsStackable($itemConfig['isStackable'])
                ->setIsHideable($itemConfig['isHideable'])
                ->setIsMovable($itemConfig['isMoveable'])
                ->setIsFireDestroyable($itemConfig['isFireDestroyable'])
                ->setIsFireBreakable($itemConfig['isFireBreakable'])
            ;
            $configs->add($config);
        }

        return $configs;
    }
}
