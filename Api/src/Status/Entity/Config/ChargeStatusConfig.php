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
    private string $chargeVisibility = VisibilityEnum::PUBLIC;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private string $chargeStrategy = ChargeStrategyTypeEnum::NONE;

    /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private ?int $threshold = null;

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
}
