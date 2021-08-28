<?php

namespace Mush\Status\Entity\Config;

use Doctrine\ORM\Mapping as ORM;
use Mush\RoomLog\Enum\VisibilityEnum;
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
    protected string $chargeVisibility = VisibilityEnum::PUBLIC;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected string $chargeStrategy = ChargeStrategyTypeEnum::NONE;

    /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    protected int $startingCharge = 0;

    /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    protected ?int $threshold = null;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    protected bool $autoRemove = false;

    public function getChargeVisibility(): string
    {
        return $this->chargeVisibility;
    }

    /**
     * @return static
     */
    public function setChargeVisibility(string $chargeVisibility): ChargeStatusConfig
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
    public function setChargeStrategy(string $chargeStrategy): ChargeStatusConfig
    {
        $this->chargeStrategy = $chargeStrategy;

        return $this;
    }

    public function getStartingCharge(): int
    {
        return $this->startingCharge;
    }

    /**
     * @return static
     */
    public function setStartingCharge(int $startingCharge): ChargeStatusConfig
    {
        $this->startingCharge = $startingCharge;

        return $this;
    }

    public function getThreshold(): ?int
    {
        return $this->threshold;
    }

    /**
     * @return static
     */
    public function setThreshold(int $threshold): ChargeStatusConfig
    {
        $this->threshold = $threshold;

        return $this;
    }

    public function isAutoRemove(): bool
    {
        return $this->autoRemove;
    }

    /**
     * @return static
     */
    public function setAutoRemove(bool $autoRemove): ChargeStatusConfig
    {
        $this->autoRemove = $autoRemove;

        return $this;
    }
}
