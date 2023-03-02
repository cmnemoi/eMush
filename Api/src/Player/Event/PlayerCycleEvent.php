<?php

namespace Mush\Player\Event;

use Mush\Game\Event\AbstractGameEvent;
use Mush\Player\Entity\Player;

class PlayerCycleEvent extends AbstractGameEvent
{
    public const PLAYER_NEW_CYCLE = 'player.new.cycle';
    public const PLAYER_NEW_DAY = 'player.new.day';

    public function __construct(
        Player $player,
        array $tags,
        \DateTime $time
    ) {
        parent::__construct($tags, $time);

        $this->player = $player;
    }

    public function getPlayer(): Player
    {
        $player = $this->player;

        if ($player === null) {
            throw new \Exception('applyEffectEvent should have a player');
        }

        return $player;
    }
}
