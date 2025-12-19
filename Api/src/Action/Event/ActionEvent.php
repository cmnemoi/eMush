<?php

namespace Mush\Action\Event;

use Mush\Action\Entity\ActionConfig;
use Mush\Action\Entity\ActionProviderInterface;
use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Exploration\Entity\Planet;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerHighlightSourceEventInterface;
use Mush\Player\ValueObject\PlayerHighlightTargetInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Skill\Entity\Skill;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\PlaceStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Triumph\Enum\TriumphTarget;
use Mush\Triumph\Event\TriumphSourceEventInterface;
use Mush\Triumph\Event\TriumphSourceEventTrait;

class ActionEvent extends AbstractGameEvent implements TriumphSourceEventInterface, PlayerHighlightSourceEventInterface
{
    use TriumphSourceEventTrait;

    public const string PRE_ACTION = 'pre.action';
    public const string POST_ACTION = 'post.action';
    public const string RESULT_ACTION = 'result.action';
    public const string EXECUTE_ACTION = 'execute.action';
    public const string FORCED_GET_UP = 'forced.get.up';

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
        if ($actionTarget instanceof GameEquipment) {
            $this->addTag('action_on_equipment');
        }
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

    public function getPlayerActionTargetOrThrow(): Player
    {
        $player = $this->getActionTarget();

        return $player instanceof Player ? $player : throw new \LogicException('Action target is not a player');
    }

    public function getPlayerActionTarget(): Player
    {
        $player = $this->getActionTarget();

        return $player instanceof Player ? $player : Player::createNull();
    }

    public function getActionTargetAsPlanet(): Planet
    {
        return $this->actionTarget instanceof Planet ? $this->actionTarget : throw new \RuntimeException('Action target is not a planet');
    }

    public function getDoorActionTargetOrThrow(): Door
    {
        $door = $this->getActionTarget();

        return $door instanceof Door ? $door : throw new \RuntimeException('Action target is not a door');
    }

    public function getEquipmentActionTargetOrThrow(): GameEquipment
    {
        $equipment = $this->getActionTarget();

        return $equipment instanceof GameEquipment ? $equipment : throw new \RuntimeException('Action target is not a game equipment');
    }

    public function getActionParameters(): array
    {
        return $this->actionParameters;
    }

    public function getActionResult(): ?ActionResult
    {
        return $this->actionResult;
    }

    public function getActionResultOrThrow(): ActionResult
    {
        $actionResult = $this->actionResult;

        return $actionResult instanceof ActionResult ? $actionResult : throw new \RuntimeException('Action does not have a result');
    }

    public function setActionResult(ActionResult $actionResult): self
    {
        $this->actionResult = $actionResult;
        $this->addTags($actionResult->getResultTags());

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

    public function getActionNameAsString(): string
    {
        return $this->actionConfig->getActionName()->toString();
    }

    public function shouldTriggerRoomTrap(): bool
    {
        $authorInteractsWithRoomEquipmentOrShelfItem = $this->actionProvider instanceof GameEquipment && $this->actionProvider->shouldTriggerRoomTrap();
        // shouldTriggerRoomTrap always return true on equipments and doors, and returns true on items if their holder is a Place (i.e. they're in a shelf).
        $actionShouldAlwaysTriggerRoomTrap = $this->actionConfig->shouldAlwaysTriggerRoomTrap();

        // @TODO --> in actions that check for ingredients in both inventory and shelf (i.e. Planting, Grafting, Building), we should create a "required interacting with shelf" event tag if at least one ingredient was taken from the shelf rather than personal inventory

        return $this->getActionName() !== ActionEnum::MOVE
            && $this->doesNotHaveTag(self::FORCED_GET_UP)
            && $this->getPlace()->hasStatus(PlaceStatusEnum::MUSH_TRAPPED->toString())
            && ($authorInteractsWithRoomEquipmentOrShelfItem || $actionShouldAlwaysTriggerRoomTrap);
    }

    public function shouldCreateParfumeAntiqueImmunizedStatus(): bool
    {
        return $this->author?->hasSkill(SkillEnum::ANTIQUE_PERFUME)
            && $this->hasAnyTag([
                ActionEnum::TAKE_SHOWER->toString(),
                ActionEnum::WASH_IN_SINK->toString(),
            ]);
    }

    public function shouldRemoveTargetLyingDownStatus(): bool
    {
        $actionTarget = $this->getActionTarget();
        $isPlayerLaidDown = $actionTarget instanceof Player && $actionTarget->hasStatus(PlayerStatusEnum::LYING_DOWN);
        $actionShouldRemoveLaidDownStatus = $this->hasAnyTag(ActionEnum::getForceGetUpActions());

        return $isPlayerLaidDown && $actionShouldRemoveLaidDownStatus;
    }

    public function shouldMakePlayerWakeUp(): bool
    {
        $player = $this->getPlayerActionTarget();

        return $player->hasStatus(PlayerStatusEnum::LYING_DOWN) && $this->hasAnyTag(ActionEnum::getForceGetUpActions());
    }

    public function actionResultDoesNotHaveContent(): bool
    {
        return $this->getActionResult()?->doesNotHaveContent() ?? false;
    }

    public function shouldMakeMycoAlarmRing(): bool
    {
        $action = $this->getActionName();
        $place = $this->getPlace();

        return $place->hasOperationalEquipmentByName(ItemEnum::MYCO_ALARM) && $action->isDetectedByMycoAlarm();
    }

    public function shouldTriggerAttemptHandling(): bool
    {
        return $this->getActionConfig()->getSuccessRate() < 100 || $this->getActionConfig()->getActionCost() > 0;
    }

    /** @param array<ActionEnum> $actions */
    public function isIn(array $actions): bool
    {
        return \in_array($this->getActionName(), $actions, true);
    }

    public function getDaedalusId(): int
    {
        return $this->getAuthor()->getDaedalus()->getId();
    }

    public function getDaedalus(): Daedalus
    {
        return $this->getAuthor()->getDaedalus();
    }

    public function hasHighlightTarget(): bool
    {
        return $this->actionTarget instanceof PlayerHighlightTargetInterface
            || $this->getActionTarget() === null;
    }

    public function getHighlightName(): string
    {
        return $this->getActionName()->toString();
    }

    public function getHighlightResult(): string
    {
        return $this->getActionResultOrThrow()->getName();
    }

    public function getHighlightTarget(): PlayerHighlightTargetInterface
    {
        $target = $this->getActionTarget();

        if (!$target) {
            $target = $this->getAuthor()->getPlace();
        }

        if (!$target instanceof PlayerHighlightTargetInterface) {
            throw new \LogicException(\sprintf(
                'Action target %s does not implement PlayerHighlightTargetInterface',
                \get_class($target)
            ));
        }

        return $target;
    }

    protected function addEventTags(): void
    {
        if ($this->actionConfig->getActionName() === ActionEnum::FLIRT) {
            $target = $this->getPlayerActionTargetOrThrow();
            if ($target->hasFlirtedWith($this->getAuthor())) {
                $this->addTag(self::LOVE_BACK);
            }
        }
    }

    protected function getEventSpecificTargets(TriumphTarget $targetSetting, PlayerCollection $scopeTargets): PlayerCollection
    {
        return match ($targetSetting) {
            TriumphTarget::AUTHOR => $scopeTargets->filter(fn (Player $player) => $player->equals($this->getAuthor())),
            TriumphTarget::PARTICIPANT => $scopeTargets->filter(fn (Player $player) => $player === $this->getAuthor() || $player === $this->getPlayerActionTarget()),
            default => throw new \LogicException("Triumph target {$targetSetting->toString()} is not supported"),
        };
    }

    private function getActionTargetOrThrow(): LogParameterInterface
    {
        $actionTarget = $this->getActionTarget();

        return $actionTarget instanceof LogParameterInterface ? $actionTarget : throw new \LogicException('Action target is not a LogParameterInterface');
    }
}
