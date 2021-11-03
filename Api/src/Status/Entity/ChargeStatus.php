<?php

namespace Mush\Status\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class ChargeStatus.
 *
 * @ORM\Entity
 */
class ChargeStatus extends Status
{
    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $charge = 0;

    public function __construct(StatusHolderInterface $statusHolder, ChargeStatusConfig $statusConfig)
    {
        parent::__construct($statusHolder, $statusConfig);

        $this->charge = $this->getStatusConfig()->getStartCharge();
    }

    public function getStatusConfig(): ChargeStatusConfig
    {
        if (!$this->statusConfig instanceof ChargeStatusConfig) {
            throw new UnexpectedTypeException($this->statusConfig, ChargeStatusConfig::class);
        }

        return $this->statusConfig;
    }

    public function getChargeVisibility(): string
    {
        return $this->getStatusConfig()->getChargeVisibility();
    }

    public function getCharge(): int
    {
        return $this->charge;
    }

    /**
     * @return static
     */
    public function addCharge(int $charge): self
    {
        $this->charge += $charge;

        return $this;
    }

    /**
     * @return static
     */
    public function setCharge(int $charge): self
    {
        $this->charge = $charge;

        return $this;
    }

    public function getStrategy(): ?string
    {
        return $this->getStatusConfig()->getChargeStrategy();
    }

    public function getDischargeStrategy(): ?string
    {
        return $this->getStatusConfig()->getDischargeStrategy();
    }

    public function getThreshold(): ?int
    {
        return $this->getStatusConfig()->getMaxCharge();
    }

    public function isAutoRemove(): bool
    {
        return $this->getStatusConfig()->isAutoRemove();
    }
}
