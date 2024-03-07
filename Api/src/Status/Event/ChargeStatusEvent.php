<?php

namespace Mush\Status\Event;

use Mush\Game\Entity\GameVariable;
use Mush\Game\Event\VariableEventInterface;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\StatusHolderInterface;

class ChargeStatusEvent extends StatusEvent implements VariableEventInterface
{
    private float $quantity;

    public function __construct(
        ChargeStatus $status,
        StatusHolderInterface $holder,
        int $quantity,
        array $tags,
        \DateTime $time
    ) {
        parent::__construct($status, $holder, $tags, $time);
        $this->setQuantity($quantity);
        $this->setVisibility($status->getChargeVisibility());
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

        if ($quantity < 0) {
            $key = array_search(VariableEventInterface::GAIN, $this->tags);

            if ($key === false) {
                $this->addTag(VariableEventInterface::LOSS);
            } elseif (!in_array(VariableEventInterface::LOSS, $this->tags)) {
                $this->tags[$key] = VariableEventInterface::LOSS;
            }
        } elseif ($quantity > 0) {
            $key = array_search(VariableEventInterface::LOSS, $this->tags);

            if ($key === false) {
                $this->addTag(VariableEventInterface::GAIN);
            } elseif (!in_array(VariableEventInterface::GAIN, $this->tags)) {
                $this->tags[$key] = VariableEventInterface::GAIN;
            }
        }

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

    public function getLogParameters(): array
    {
        $parameters = parent::getLogParameters();
        $parameters['quantity'] = $this->getQuantity();

        return $parameters;
    }
}
