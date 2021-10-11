<?php

namespace Mush\Daedalus\Event;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Player\Entity\Player;
use Mush\Game\Event\AbstractQuantityEvent;

class DaedalusModifierEvent extends DaedalusEvent implements AbstractQuantityEvent
{
    public const CHANGE_HULL = 'change.hull';
    public const CHANGE_OXYGEN = 'change.oxygen';
    public const CHANGE_FUEL = 'change.fuel';

    private int $quantity;
    private ?Player $player;

    public function __construct(
        Daedalus $daedalus,
        int $quantity,
        string $reason,
        \DateTime $time
    ) {
        $this->quantity = $quantity;

        parent::__construct($daedalus, $reason, $time);
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(Player $player): DaedalusModifierEvent
    {
        $this->player = $player;

        return $this;
    }
}
