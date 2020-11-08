<?php

namespace Mush\Action\Event;

use Mush\Player\Entity\Player;
use Symfony\Contracts\EventDispatcher\Event;

class ActionEvent extends Event
{
    public const PRE_ACTION = 'pre.action';
    public const POST_ACTION = 'post.action';

    private Player $player;
    private string $action;

    public function __construct(string $action, Player $player)
    {
        $this->action = $action;
        $this->player = $player;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getAction(): string
    {
        return $this->action;
    }
}
