<?php

namespace Mush\Player\Event;

use Mush\Player\Entity\ActionModifier;
use Mush\Player\Entity\Player;
use Symfony\Contracts\EventDispatcher\Event;

class PlayerEvent extends Event
{
    public const NEW_PLAYER = 'new.player';
    public const DEATH_PLAYER = 'death.player';
    public const MODIFIER_PLAYER = 'modifier.player';
    public const INFECTION_PLAYER = 'infection.player';
    public const CONVERSION_PLAYER = 'conversion.player';

    private Player $player;
    private ?ActionModifier $actionModifier = null;
    private ?string $reason = null;
    private \DateTime $time;

    public function __construct(Player $player, $time = null)
    {
        $this->time = $time ?? new \DateTime();
        $this->player = $player;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getTime(): \DateTime
    {
        return $this->time;
    }

    public function getActionModifier(): ?ActionModifier
    {
        return $this->actionModifier;
    }

    public function setActionModifier(ActionModifier $actionModifier): PlayerEvent
    {
        $this->actionModifier = $actionModifier;

        return $this;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): PlayerEvent
    {
        $this->reason = $reason;

        return $this;
    }
}
