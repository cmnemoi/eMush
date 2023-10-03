<?php

namespace Mush\Daedalus\Event;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\GameVariable;
use Mush\Game\Event\VariableEventInterface;

class DaedalusVariableEvent extends DaedalusEvent implements VariableEventInterface
{
    private float $quantity;
    private string $variableName;

    public function __construct(
        Daedalus $daedalus,
        string $variableName,
        float $quantity,
        array $tags,
        \DateTime $time
    ) {
        $this->variableName = $variableName;

        parent::__construct($daedalus, $tags, $time);

        $this->setQuantity($quantity);
    }

    public function setQuantity(float $quantity): self
    {
        $this->quantity = $quantity;

        if ($quantity < 0) {
            $key = array_search(VariableEventInterface::GAIN, $this->tags);

            if ($key === false) {
                $this->tags[] = VariableEventInterface::LOSS;
            } elseif (!in_array(VariableEventInterface::LOSS, $this->tags)) {
                $this->tags[$key] = VariableEventInterface::LOSS;
            }
        } elseif ($quantity > 0) {
            $key = array_search(VariableEventInterface::LOSS, $this->tags);

            if ($key === false) {
                $this->tags[] = VariableEventInterface::GAIN;
            } elseif (!in_array(VariableEventInterface::GAIN, $this->tags)) {
                $this->tags[$key] = VariableEventInterface::GAIN;
            }
        }

        return $this;
    }

    public function getQuantity(): float
    {
        return $this->quantity;
    }

    public function getRoundedQuantity(): int
    {
        return intval($this->quantity);
    }

    public function getVariable(): GameVariable
    {
        return $this->daedalus->getVariableByName($this->variableName);
    }

    public function getVariableName(): string
    {
        return $this->variableName;
    }
}
