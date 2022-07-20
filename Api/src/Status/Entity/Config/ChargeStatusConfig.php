<?php

namespace Mush\Status\Entity\Config;

use Doctrine\ORM\Mapping as ORM;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Status\Enum\ChargeStrategyTypeEnum;

/**
 * Class ChargeStatusConfig.
 *
 * @ORM\Entity()
 */
class ChargeStatusConfig extends StatusConfig
{
    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private string $chargeVisibility = VisibilityEnum::PUBLIC;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private string $chargeStrategy = ChargeStrategyTypeEnum::NONE;

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
    private string $dischargeStrategy = ChargeStrategyTypeEnum::NONE;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $autoRemove = false;

    public function getChargeVisibility(): string
    {
        return $this->chargeVisibility;
    }

    /**
     * @return static
     */
    public function setChargeVisibility(string $chargeVisibility): self
    {
        $this->chargeVisibility = $chargeVisibility;

        return $this;
    }

    public function getChargeStrategy(): string
    {
        return $this->chargeStrategy;
    }

    /**
     * @return static
     */
    public function setChargeStrategy(string $chargeStrategy): self
    {
        $this->chargeStrategy = $chargeStrategy;

        return $this;
    }

    public function isAutoRemove(): bool
    {
        return $this->autoRemove;
    }

    /**
     * @return static
     */
    public function setAutoRemove(bool $autoRemove): self
    {
        $this->autoRemove = $autoRemove;

        return $this;
    }

    public function getMaxCharge(): int
    {
        return $this->maxCharge;
    }

    /**
     * @return static
     */
    public function setMaxCharge(int $maxCharge): self
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
    public function setStartCharge(int $startCharge): self
    {
        $this->startCharge = $startCharge;

        return $this;
    }

    /**
     * @return static
     */
    public function setDischargeStrategy(string $dischargeStrategy): self
    {
        $this->dischargeStrategy = $dischargeStrategy;

        return $this;
    }

    public function getDischargeStrategy(): string
    {
        return $this->dischargeStrategy;
    }
}
