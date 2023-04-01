<?php

namespace Mush\Hunter\Event;

use Mush\Game\Entity\GameVariable;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Hunter\Entity\Hunter;
use Mush\Modifier\Entity\ModifierHolder;

class HunterVariableEvent extends HunterEvent implements VariableEventInterface
{
    private int $quantity;
    private string $variableName;

    public function __construct(Hunter $hunter, string $variableName, int $quantity, array $tags, \DateTime $time)
    {
        parent::__construct($hunter, VisibilityEnum::PRIVATE, $tags, $time);

        $this->hunter = $hunter;
        $this->quantity = $quantity;
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

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getVariableHolder(): Hunter
    {
        return $this->hunter;
    }

    public function getModifierHolder(): ModifierHolder
    {
        return $this->hunter;
    }
}
