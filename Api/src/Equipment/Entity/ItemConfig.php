<?php

namespace Mush\Equipment\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Action\Enum\ActionEnum;

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
    private bool $isHeavy;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $isStackable;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $isHideable;

    public function createGameItem(): GameItem
    {
        $gameItem = new GameItem();
        $gameItem
            ->setName($this->getName())
            ->setEquipment($this)
        ;

        return $gameItem;
    }

    public function isHeavy(): bool
    {
        return $this->isHeavy;
    }

    /**
     * @return static
     */
    public function setIsHeavy(bool $isHeavy): ItemConfig
    {
        $this->isHeavy = $isHeavy;

        return $this;
    }

    public function isStackable(): bool
    {
        return $this->isStackable;
    }

    /**
     * @return static
     */
    public function setIsStackable(bool $isStackable): ItemConfig
    {
        $this->isStackable = $isStackable;

        return $this;
    }

    public function isHideable(): bool
    {
        return $this->isHideable;
    }

    /**
     * @return static
     */
    public function setIsHideable(bool $isHideable): ItemConfig
    {
        $this->isHideable = $isHideable;

        return $this;
    }

    public function getActions(): Collection
    {
        return parent::getActions();
        $actions = array_merge(ActionEnum::getPermanentItemActions(), parent::getActions()->toArray());

        return new ArrayCollection($actions);
    }
}
