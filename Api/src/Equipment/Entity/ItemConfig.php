<?php

namespace Mush\Equipment\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\EquipmentConfig;

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
    private bool $isTakeable;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $isDropable;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $isStackable;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $isHideable;


    public function isHeavy(): bool
    {
        return $this->isHeavy;
    }

    public function setIsHeavy(bool $isHeavy): ItemConfig
    {
        $this->isHeavy = $isHeavy;

        return $this;
    }

    public function isTakeable(): bool
    {
        return $this->isTakeable;
    }

    public function setIsTakeable(bool $isTakeable): ItemConfig
    {
        $this->isTakeable = $isTakeable;

        return $this;
    }

    public function isDropable(): bool
    {
        return $this->isDropable;
    }

    public function setIsDropable(bool $isDropable): ItemConfig
    {
        $this->isDropable = $isDropable;

        return $this;
    }

    public function isStackable(): bool
    {
        return $this->isStackable;
    }

    public function setIsStackable(bool $isStackable): ItemConfig
    {
        $this->isStackable = $isStackable;

        return $this;
    }

    public function isHideable(): bool
    {
        return $this->isHideable;
    }

    public function setIsHideable(bool $isHideable): ItemConfig
    {
        $this->isHideable = $isHideable;

        return $this;
    }

    public function getActions(): Collection
    {
        $actions = ActionEnum::getPermanentItemActions();

        foreach ($this->getMechanics() as $mechanic) {
            $actions = array_merge($actions, $mechanic->getActions());
        }

        return new ArrayCollection($actions);
    }
}
