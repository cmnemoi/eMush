<?php

namespace Mush\Action\Event;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\Entity\Action;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Event\ModifiableEventInterface;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameterInterface;

class ActionEvent extends AbstractGameEvent implements ModifiableEventInterface
{
    public const PRE_ACTION = 'pre.action';
    public const POST_ACTION = 'post.action';
    public const RESULT_ACTION = 'result.action';

    private Player $player;
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
        return $this->player;
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
        $modifiers = $this->player->getAllModifiers()->getNoActionParameterModifiers();

        $parameter = $this->actionParameter;
        if ($parameter instanceof GameEquipment) {
            $modifiers->addModifiers($parameter->getModifiers()->getActionParameterModifiers());
        } else if ($parameter instanceof Player) {
            $modifiers->addModifiers($parameter->getModifiers()->getActionParameterModifiers());
        }

        return $modifiers;
    }
}
