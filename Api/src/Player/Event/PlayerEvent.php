<?php

namespace Mush\Player\Event;

use Mush\Player\Entity\Player;

class PlayerEvent extends PlayerCycleEvent
{
    public const NEW_PLAYER = 'new.player';
    public const DEATH_PLAYER = 'death.player';
    public const METAL_PLATE = 'metal.plate';
    public const PANIC_CRISIS = 'panic.crisis';
    public const INFECTION_PLAYER = 'infection.player';
    public const CONVERSION_PLAYER = 'conversion.player';
    public const END_PLAYER = 'end.player';

    public function getPlayer(): Player
    {
        return $this->player;
    }
}
