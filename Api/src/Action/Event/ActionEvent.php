<?php

namespace Mush\Action\Event;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\Entity\ActionCost;
use Mush\Player\Entity\Player;
use Symfony\Contracts\EventDispatcher\Event;

class ActionEvent extends Event
{
    public const PRE_ACTION = 'pre.action';
    public const POST_ACTION = 'post.action';

    private Player $player;
    private string $action;
    private ActionCost $actionCost;
    private ?ActionResult $actionResult = null;

    public function __construct(string $action, Player $player, ActionCost $actionCost)
    {
        $this->action = $action;
        $this->player = $player;
        $this->actionCost = $actionCost;
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
