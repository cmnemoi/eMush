<?php

namespace Mush\Item\Entity\Items;

use Doctrine\ORM\Mapping as ORM;
use Mush\Item\Entity\ItemType;
use Mush\Item\Enum\ItemTypeEnum;

/**
 * Class Item.
 *
 * @ORM\Entity
 */
class Charged extends ItemType
{
    protected string $type = ItemTypeEnum::CHARGED;

     /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $maxCharge = 0;

    /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $startCharge = 0;

     /**
     * @ORM\Column(type="string", nullable=false)
     */
    private string $chargeStrategy;

     /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $isVisible = true;



    public function getMaxCharge(): int
    {
        return $this->maxCharge;
    }

    public function setMaxCharge(int $maxCharge): Charged
    {
        $this->maxCharge = $maxCharge;

        return $this;
    }

    public function getStartCharge(): int
    {
        return $this->startCharge;
    }

    public function setStartCharge(int $startCharge): Charged
    {
        $this->startCharge = $startCharge;

        return $this;
    }


    public function getChargeStrategy(): string
    {
        return $this->chargeStrategy;
    }

    public function setChargeStrategy(string $chargeStrategy): Charged
    {
        $this->chargeStrategy = $chargeStrategy;

        return $this;
    }

    public function getIsVisible(): bool
    {
        return $this->isVisible;
    }

    public function setIsVisible(bool $isVisible): Charged
    {
        $this->isVisible = $isVisible;

        return $this;
    }
}
