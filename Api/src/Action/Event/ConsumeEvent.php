<?php

namespace Mush\Action\Event;

use Mush\Equipment\Entity\GameItem;
use Mush\Player\Entity\Player;
use Symfony\Contracts\EventDispatcher\Event;

class ConsumeEvent extends Event
{
    public const CONSUME = 'action.consume';

    private Player $player;
    private GameItem $gameItem;

    public function __construct(Player $player, GameItem $gameItem)
    {
        $this->player = $player;
        $this->gameItem = $gameItem;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getGameItem(): GameItem
    {
        return $this->gameItem;
    }
}
