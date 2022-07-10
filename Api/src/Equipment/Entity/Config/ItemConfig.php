<?php

namespace Mush\Equipment\Entity\Config;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\RoomLog\Enum\LogParameterKeyEnum;

/**
 * Class ItemConfig.
 *
 * @ORM\Entity
 */
class ItemConfig extends EquipmentConfig
{
    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $isStackable;

    public function createGameItem(): GameItem
    {
        $gameItem = new GameItem();
        $gameItem
            ->setName($this->getShortName())
            ->setEquipment($this)
        ;

        return $gameItem;
    }

    public function isStackable(): bool
    {
        return $this->isStackable;
    }

    /**
     * @return static
     */
    public function setIsStackable(bool $isStackable): self
    {
        $this->isStackable = $isStackable;

        return $this;
    }

    public function getActions(): Collection
    {
        return parent::getActions();
        $actions = array_merge(ActionEnum::getPermanentItemActions(), parent::getActions()->toArray());

        return new ArrayCollection($actions);
    }

    public function getLogKey(): string
    {
        return LogParameterKeyEnum::ITEM;
    }
}
