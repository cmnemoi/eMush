<?php

namespace Mush\Action\Event;

use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameterInterface;

class ActionEvent extends AbstractGameEvent
{
    public const PRE_ACTION = 'pre.action';
    public const POST_ACTION = 'post.action';
    public const RESULT_ACTION = 'result.action';
    public const EXECUTE_ACTION = 'execute.action';

    private Action $action;
    private ?LogParameterInterface $actionParameter;
    private ?ActionResult $actionResult = null;

    public function __construct(Action $action, Player $player, ?LogParameterInterface $actionParameter)
    {
        $this->action = $action;
        $this->author = $player;
        $this->actionParameter = $actionParameter;

        $tags = $action->getActionTags();
        if ($actionParameter !== null) {
            $tags[] = $actionParameter->getLogName();
        }

        parent::__construct($tags, new \DateTime());
    }

    public function getAuthor(): Player
    {
        $player = $this->author;
        if ($player === null) {
            throw new \Exception('applyEffectEvent should have a player');
        }

        return $player;
    }

    public function getAction(): Action
    {
        return $this->action;
    }

    public function getActionParameter(): ?LogParameterInterface
    {
        return $this->actionParameter;
    }

    public function getActionResult(): ?ActionResult
    {
        return $this->actionResult;
    }

    public function setActionResult(?ActionResult $actionResult): self
    {
        $this->actionResult = $actionResult;

        return $this;
    }

    public function getModifiers(): ModifierCollection
    {
        $modifiers = $this->getAuthor()->getAllModifiers()->getEventModifiers($this)->getTargetModifiers(false);

        $parameter = $this->actionParameter;
        if ($parameter instanceof ModifierHolderInterface) {
            $modifiers = $modifiers->addModifiers($parameter->getAllModifiers()->getEventModifiers($this)->getTargetModifiers(true));
        }

        return $modifiers;
    }
}
