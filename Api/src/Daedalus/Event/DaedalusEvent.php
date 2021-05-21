<?php

namespace Mush\Daedalus\Event;

use Mush\Daedalus\Entity\Daedalus;
use Symfony\Contracts\EventDispatcher\Event;

class DaedalusEvent extends Event
{
    public const NEW_DAEDALUS = 'new.daedalus';
    public const END_DAEDALUS = 'end.daedalus';
    public const FULL_DAEDALUS = 'full.daedalus';
    public const CHANGE_HULL = 'change.hull';
    public const CHANGE_OXYGEN = 'change.oxygen';
    public const CHANGE_FUEL = 'change.fuel';

    private Daedalus $daedalus;
    private ?string $reason = null;
    private \DateTime $time;
    private ?int $quantity = null;

    public function __construct(Daedalus $daedalus, ?\DateTime $time)
    {
        $this->time = $time ?? new \DateTime();

        $this->daedalus = $daedalus;
    }

    public function getDaedalus(): Daedalus
    {
        return $this->daedalus;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): DaedalusEvent
    {
        $this->reason = $reason;

        return $this;
    }

    public function getTime(): \DateTime
    {
        return $this->time;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): DaedalusEvent
    {
        $this->quantity = $quantity;

        return $this;
    }
}
