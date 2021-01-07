<?php

namespace Mush\Action\Event;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\Entity\Action;
use Mush\Player\Entity\Player;
use Symfony\Contracts\EventDispatcher\Event;

class ActionEvent extends Event
{
    public const PRE_ACTION = 'pre.action';
    public const POST_ACTION = 'post.action';

    private Player $player;
    private Action $action;
    private ?ActionResult $actionResult = null;

    public function __construct(Action $action, Player $player)
    {
        $this->action = $action;
        $this->player = $player;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getAction(): Action
    {
        return $this->action;
    }

    public function getActionResult(): ?ActionResult
    {
        return $this->actionResult;
    }

    public function setActionResult(?ActionResult $actionResult): ActionEvent
    {
        $this->actionResult = $actionResult;

        return $this;
    }
}
