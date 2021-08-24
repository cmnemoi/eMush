<?php

namespace Mush\Action\Event;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\Entity\Action;
use Mush\Game\Event\AbstractMushEvent;
use Mush\Player\Entity\Player;
use Symfony\Contracts\EventDispatcher\Event;

class ActionEvent extends AbstractMushEvent
{
    public const PRE_ACTION = 'pre.action';
    public const POST_ACTION = 'post.action';
    public const RESULT_ACTION = 'result.action';

    private Player $player;
    private Action $action;
    private ?ActionParameter $actionParameter;
    private ?ActionResult $actionResult = null;

    public function __construct(Action $action, Player $player, ?ActionParameter $actionParameter)
    {
        $this->action = $action;
        $this->player = $player;

        parent::__construct($action->getName(), new \DateTime());
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getAction(): Action
    {
        return $this->action;
    }

    public function getActionParameter(): ?ActionParameter
    {
        return $this->actionParameter;
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
