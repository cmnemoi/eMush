<?php

namespace Mush\Hunter\Event;

use Mush\Game\Entity\GameVariable;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Hunter\Entity\Hunter;

class HunterVariableEvent extends HunterEvent implements VariableEventInterface
{
    private float $quantity;
    private string $variableName;

    public function __construct(Hunter $hunter, string $variableName, int $quantity, array $tags, \DateTime $time)
    {
        parent::__construct($hunter, VisibilityEnum::PRIVATE, $tags, $time);

        $this->hunter = $hunter;
        $this->setQuantity($quantity);
        $this->variableName = $variableName;
    }

    public function getLogParameters(): array
    {
        return [
            $this->hunter->getLogKey() => $this->hunter->getLogName(),
            'quantity' => abs($this->quantity),
        ];
    }

    public function getVariable(): GameVariable
    {
        return $this->hunter->getVariableByName($this->variableName);
    }

    public function getVariableName(): string
    {
        return $this->variableName;
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
}
