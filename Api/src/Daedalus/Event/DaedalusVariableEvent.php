<?php

namespace Mush\Daedalus\Event;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Player\Entity\Player;

class DaedalusVariableEvent extends DaedalusEvent implements AbstractQuantityEvent
{
    private int $quantity;
    private string $modifiedVariable;
    private ?Player $player = null;

    public function __construct(
        Daedalus $daedalus,
        string $modifiedVariable,
        int $quantity,
        string $reason,
        \DateTime $time
    ) {
        $this->modifiedVariable = $modifiedVariable;
        $this->quantity = $quantity;

        parent::__construct($daedalus, $reason, $time);
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

    public function getModifiedVariable(): string
    {
        return $this->modifiedVariable;
    }

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(Player $player): self
    {
        $this->player = $player;

        return $this;
    }

    public function getModifierHolder(): ModifierHolder
    {
        if ($this->player !== null) {
            return $this->player;
        }

        return $this->daedalus;
    }
}
