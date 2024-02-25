<?php

namespace Mush\Action\Event;

use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\DaedalusStatusEnum;

class ActionEvent extends AbstractGameEvent
{
    public const PRE_ACTION = 'pre.action';
    public const POST_ACTION = 'post.action';
    public const RESULT_ACTION = 'result.action';
    public const EXECUTE_ACTION = 'execute.action';

    private Action $action;
    private ?LogParameterInterface $actionTarget;
    private ?ActionResult $actionResult = null;
    private array $actionParameters = [];

    public function __construct(Action $action, Player $player, LogParameterInterface $actionTarget = null, array $actionParameters = [])
    {
        $this->action = $action;
        $this->author = $player;
        $this->actionTarget = $actionTarget;
        $this->actionParameters = $actionParameters;

        parent::__construct($action->getActionTags(), new \DateTime());

        if ($actionTarget !== null) {
            $this->addTag($actionTarget->getLogName());
        }

        $daedalus = $this->author->getDaedalus();
        if ($daedalus->hasStatus(DaedalusStatusEnum::NO_GRAVITY) || $daedalus->hasStatus(DaedalusStatusEnum::NO_GRAVITY_REPAIRED)) {
            $this->addTag(DaedalusStatusEnum::NO_GRAVITY);
        }
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

    public function getActionTarget(): ?LogParameterInterface
    {
        return $this->actionTarget;
    }

    public function getActionParameters(): array
    {
        return $this->actionParameters;
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

    public function getModifiersByPriorities(array $priorities): ModifierCollection
    {
        $modifiers = $this->getAuthor()->getAllModifiers()->getEventModifiers($this, $priorities)->getTargetModifiers(false);

        $actionTarget = $this->actionTarget;
        if ($actionTarget instanceof ModifierHolderInterface) {
            $modifiers = $modifiers->addModifiers(
                $actionTarget->getAllModifiers()->getEventModifiers($this, $priorities)->getTargetModifiers(true)
            );
        }

        return $modifiers;
    }
}
