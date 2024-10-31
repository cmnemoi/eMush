<?php

namespace Mush\Action\Event;

use Mush\Action\Entity\ActionConfig;
use Mush\Action\Entity\ActionProviderInterface;
use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Exploration\Entity\Planet;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Game\Service\Random\D100RollServiceInterface;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Skill\Entity\Skill;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\PlaceStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;

class ActionEvent extends AbstractGameEvent
{
    public const string PRE_ACTION = 'pre.action';
    public const string POST_ACTION = 'post.action';
    public const string RESULT_ACTION = 'result.action';
    public const string EXECUTE_ACTION = 'execute.action';
    private const OBSERVANT_REVEAL_CHANCE = 25;

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

    public function shouldBeAnonymous(): bool
    {
        return $this->getActionName() === ActionEnum::HIT && $this->author?->hasSkill(SkillEnum::NINJA);
    }

    public function shouldCreateParfumeAntiqueImmunizedStatus(): bool
    {
        return $this->author?->hasSkill(SkillEnum::ANTIQUE_PERFUME) && $this->hasTag(ActionEnum::TAKE_SHOWER->value);
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

    public function shouldCreateLogNoticedLog(D100RollServiceInterface $d100Roll): bool
    {
        $player = $this->getAuthor();

        return $player->getPlace()->hasAlivePlayerWithSkill(SkillEnum::OBSERVANT) && $d100Roll->isSuccessful(self::OBSERVANT_REVEAL_CHANCE);
    }
}
