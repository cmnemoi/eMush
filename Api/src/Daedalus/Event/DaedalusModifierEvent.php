<?php

namespace Mush\Daedalus\Event;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Player\Entity\Player;
use Symfony\Contracts\EventDispatcher\Event;

class DaedalusModifierEvent extends Event
{
    public const CHANGE_HULL = 'change.hull';
    public const CHANGE_OXYGEN = 'change.oxygen';
    public const CHANGE_FUEL = 'change.fuel';

    private Daedalus $daedalus;
    private ?Player $player;
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

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(Player $player): DaedalusModifierEvent
    {
        $this->player = $player;

        return $this;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): DaedalusModifierEvent
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

    public function setQuantity(int $quantity): DaedalusModifierEvent
    {
        $this->quantity = $quantity;

        return $this;
    }
}
