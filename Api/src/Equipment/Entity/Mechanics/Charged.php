<?php

namespace Mush\Equipment\Entity\Mechanics;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\EquipmentMechanic;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Status\Entity\Config\ChargeStatusConfig;

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
     * @ORM\ManyToOne(targetEntity="Mush\Status\Entity\Config\ChargeStatusConfig")
     */
    private ChargeStatusConfig $chargeStatusConfig;

    public function getMaxCharge(): int
    {
        return $this->maxCharge;
    }

    /**
     * @return static
     */
    public function setMaxCharge(int $maxCharge): Charged
    {
        $this->maxCharge = $maxCharge;

        return $this;
    }

    public function getStartCharge(): int
    {
        return $this->startCharge;
    }

    /**
     * @return static
     */
    public function setStartCharge(int $startCharge): Charged
    {
        $this->startCharge = $startCharge;

        return $this;
    }

    public function getChargeStatusConfig(): ChargeStatusConfig
    {
        return $this->chargeStatusConfig;
    }

    /**
     * @return static
     */
    public function setChargeStatusConfig(ChargeStatusConfig $chargeStatusConfig): Charged
    {
        $this->chargeStatusConfig = $chargeStatusConfig;

        return $this;
    }
}
