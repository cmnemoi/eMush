<?php


namespace Mush\Item\Service;

use Mush\Item\Entity\Collection\ItemConfigCollection;

interface ItemConfigServiceInterface
{
    public function getConfigs(): ItemConfigCollection;
}
