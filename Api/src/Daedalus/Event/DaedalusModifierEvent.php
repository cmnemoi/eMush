<?php

namespace Mush\Daedalus\Event;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Event\AbstractQuantityEvent;

class DaedalusModifierEvent extends DaedalusEvent implements AbstractQuantityEvent
{
    public const CHANGE_HULL = 'change.hull';
    public const CHANGE_OXYGEN = 'change.oxygen';
    public const CHANGE_FUEL = 'change.fuel';

    private int $quantity;

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
}
