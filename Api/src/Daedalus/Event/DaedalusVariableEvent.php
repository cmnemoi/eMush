<?php

namespace Mush\Daedalus\Event;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\GameVariable;
use Mush\Game\Entity\GameVariableHolderInterface;
use Mush\Game\Event\VariableEventInterface;
use Mush\Modifier\Entity\ModifierHolder;

class DaedalusVariableEvent extends DaedalusEvent implements VariableEventInterface
{
    private int $quantity;
    private string $variableName;

    public function __construct(
        Daedalus $daedalus,
        string $variableName,
        int $quantity,
        array $tags,
        \DateTime $time
    ) {
        $this->variableName = $variableName;
        $this->quantity = $quantity;

        parent::__construct($daedalus, $tags, $time);
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getVariable(): GameVariable
    {
        return $this->daedalus->getVariableByName($this->variableName);
    }

    public function getVariableName(): string
    {
        return $this->variableName;
    }

    public function getVariableHolder(): GameVariableHolderInterface
    {
        return $this->daedalus;
    }

    public function getModifierHolder(): ModifierHolder
    {
        if ($this->player !== null) {
            return $this->player;
        }

        return $this->daedalus;
    }
}
