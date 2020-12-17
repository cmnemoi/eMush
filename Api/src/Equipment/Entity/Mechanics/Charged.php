<?php

namespace Mush\Equipment\Entity\Mechanics;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\EquipmentMechanic;
use Mush\Equipment\Enum\EquipmentMechanicEnum;

/**
 * Class Equipment.
 *
 * @ORM\Entity
 */
class Charged extends EquipmentMechanic
{
    protected string $mechanic = EquipmentMechanicEnum::CHARGED;

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

    public function isVisible(): bool
    {
        return $this->isVisible;
    }

    public function setIsVisible(bool $isVisible): Charged
    {
        $this->isVisible = $isVisible;

        return $this;
    }
}
