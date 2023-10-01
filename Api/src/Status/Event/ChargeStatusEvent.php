<?php

namespace Mush\Status\Event;

use Mush\Game\Entity\GameVariable;
use Mush\Game\Event\VariableEventInterface;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\StatusHolderInterface;

class ChargeStatusEvent extends StatusEvent implements VariableEventInterface
{
    public const STATUS_CHARGE_UPDATED = 'status.charge.updated';
    private float $quantity;

    public function __construct(
        ChargeStatus $status,
        StatusHolderInterface $holder,
        int $quantity,
        array $tags,
        \DateTime $time
    ) {
        parent::__construct($status, $holder, $tags, $time);
        $this->quantity = $quantity;
    }

    public function getStatus(): ChargeStatus
    {
        /** @var ChargeStatus $status */
        $status = $this->status;

        return $status;
    }

    public function getRoundedQuantity(): int
    {
        return intval($this->quantity);
    }

    public function getQuantity(): float
    {
        return $this->quantity;
    }

    public function setQuantity(float $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getVariable(): GameVariable
    {
        return $this->getStatus()->getVariableByName($this->getStatusName());
    }

    public function getVariableName(): string
    {
        return $this->getStatusName();
    }
}
