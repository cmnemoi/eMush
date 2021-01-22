<?php

namespace Mush\Player\Event;

use Mush\Game\Event\AbstractCycleEvent;
use Mush\Player\Entity\Player;

class PlayerCycleEvent extends AbstractCycleEvent
{
    public const PLAYER_NEW_CYCLE = 'player.new.cycle';
    public const PLAYER_NEW_DAY = 'player.new.day';

    private Player $player;

    public function __construct(Player $player, \DateTime $time)
    {
        parent::__construct($time);

        $this->player = $player;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }
}
