<?php

namespace Mush\Equipment\Entity\Config;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\Item;
use Mush\RoomLog\Enum\LogParameterKeyEnum;

#[ORM\Entity]
class ItemConfig extends EquipmentConfig
{
    #[ORM\Column(type: 'boolean', nullable: false)]
    private bool $isStackable;

    public function createGameItem(): Item
    {
        $gameItem = new Item();
        $gameItem
            ->setName($this->getShortName())
            ->setConfig($this)
        ;

        return $gameItem;
    }

    public function isStackable(): bool
    {
        return $this->isStackable;
    }

    public function setIsStackable(bool $isStackable): static
    {
        $this->isStackable = $isStackable;

        return $this;
    }

    public function getLogKey(): string
    {
        return LogParameterKeyEnum::ITEM;
    }
}
