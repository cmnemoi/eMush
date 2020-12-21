<?php

namespace Mush\Action\Event;

use Mush\Player\Entity\Player;
use Mush\Action\Entity\ActionCost;
use Symfony\Contracts\EventDispatcher\Event;

class ActionEvent extends Event
{
    public const PRE_ACTION = 'pre.action';
    public const POST_ACTION = 'post.action';

    private Player $player;
    private string $action;
    private ActionCost $actionCost;

    public function __construct(string $action, Player $player, ActionCost $actionCost)
    {
        $this->action = $action;
        $this->player = $player;
        $this->actionCost=$actionCost;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getActionCost(): ActionCost
    {
        return $this->actionCost;
    }
}
