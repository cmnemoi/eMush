<?php

namespace Mush\Action\Event;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\Entity\Action;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Modifier\Event\ModifiableEventInterface;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameterInterface;

class ActionEvent extends AbstractGameEvent
{
    public const PRE_ACTION = 'pre.action';
    public const POST_ACTION = 'post.action';
    public const RESULT_ACTION = 'result.action';

    private Action $action;
    private ?LogParameterInterface $actionParameter;
    private ?ActionResult $actionResult = null;

    public function __construct(Action $action, Player $player, ?LogParameterInterface $actionParameter)
    {
        $this->action = $action;
        $this->player = $player;
        $this->actionParameter = $actionParameter;

        parent::__construct($action->getActionTags(), new \DateTime());
    }

    public function getPlayer(): Player
    {
        $player = $this->player;
        if ($player === null) {
            throw new \Error('applyEffectEvent should have a player');
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
        $modifiers = $this->getPlayer()->getAllModifiers()->getEventModifiers($this)->getTargetModifiers(false);

        $parameter = $this->actionParameter;
        if ($parameter instanceof ModifierHolder) {
            $modifiers->addModifiers($parameter->getAllModifiers()->getEventModifiers($this)->getTargetModifiers(true));
        }

        return $modifiers;
    }
}
