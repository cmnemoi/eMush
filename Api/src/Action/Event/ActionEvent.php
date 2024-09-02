<?php

namespace Mush\Action\Event;

use Mush\Action\Entity\ActionConfig;
use Mush\Action\Entity\ActionProviderInterface;
use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Skill\Entity\Skill;
use Mush\Status\Enum\PlaceStatusEnum;

class ActionEvent extends AbstractGameEvent
{
    public const string PRE_ACTION = 'pre.action';
    public const string POST_ACTION = 'post.action';
    public const string RESULT_ACTION = 'result.action';
    public const string EXECUTE_ACTION = 'execute.action';

    private ActionConfig $actionConfig;
    private ActionProviderInterface $actionProvider;
    private ?LogParameterInterface $actionTarget;
    private ?ActionResult $actionResult = null;
    private array $actionParameters;

    public function __construct(
        ActionConfig $actionConfig,
        ActionProviderInterface $actionProvider,
        Player $player,
        array $tags,
        ?LogParameterInterface $actionTarget = null,
        array $actionParameters = [],
    ) {
        $this->actionConfig = $actionConfig;
        $this->actionProvider = $actionProvider;
        $this->author = $player;
        $this->actionTarget = $actionTarget;
        $this->actionParameters = $actionParameters;

        parent::__construct($tags, new \DateTime());

        $player->getSkills()->map(fn (Skill $skill) => $this->addTag($skill->getNameAsString()));
    }

    public function getAuthor(): Player
    {
        $player = $this->author;
        if ($player === null) {
            throw new \RuntimeException('applyEffectEvent should have a player');
        }

        return $player;
    }

    public function getActionConfig(): ActionConfig
    {
        return $this->actionConfig;
    }

    public function getActionProvider(): ActionProviderInterface
    {
        return $this->actionProvider;
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

    public function setActionResult(ActionResult $actionResult): self
    {
        $this->actionResult = $actionResult;
        $this->addTag($actionResult->getResultTag());

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

    public function getPlace(): Place
    {
        return $this->getAuthor()->getPlace();
    }

    public function getActionName(): ActionEnum
    {
        return $this->actionConfig->getActionName();
    }

    public function shouldTriggerRoomTrap(): bool
    {
        $authorInteractsWithRoomEquipment = $this->actionProvider instanceof GameEquipment && $this->actionProvider->shouldTriggerRoomTrap();
        $actionDoesNotInteractWithAnEquipmentButShouldTriggerRoomTrap = $this->actionProvider instanceof GameEquipment === false
            && $this->actionConfig->shouldTriggerRoomTrap();

        return $this->getPlace()->hasStatus(PlaceStatusEnum::MUSH_TRAPPED->value)
            && ($authorInteractsWithRoomEquipment || $actionDoesNotInteractWithAnEquipmentButShouldTriggerRoomTrap);
    }
}
