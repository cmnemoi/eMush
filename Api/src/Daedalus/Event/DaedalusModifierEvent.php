<?php

namespace Mush\Daedalus\Event;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Player\Entity\Player;

class DaedalusModifierEvent extends DaedalusEvent implements AbstractQuantityEvent
{
    public const CHANGE_HULL = 'change.hull';
    public const CHANGE_OXYGEN = 'change.oxygen';
    public const CHANGE_FUEL = 'change.fuel';

    private int $quantity;
    private ?Player $player = null;

    public function __construct(
        Daedalus $daedalus,
        int $quantity,
        string $reason,
        \DateTime $time
    ) {
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
