<?php

namespace Mush\Player\Event;

use Mush\Player\Entity\Player;
use Symfony\Contracts\EventDispatcher\Event;

class PlayerEvent extends Event
{
    public const NEW_PLAYER = 'new.player';
    public const DEATH_PLAYER = 'death.player';

    private Player $player;

    public function __construct(Player $player)
    {
        $this->player = $player;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }
}