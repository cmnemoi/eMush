<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionConfig;
use Mush\Action\Entity\ActionProviderInterface;
use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Error;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Enum\ActionTypeEnum;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\AdminAction;
use Mush\Action\Validator\ClassConstraint;
use Mush\Action\Validator\HasAction;
use Mush\Action\Validator\IsActionProviderOperational;
use Mush\Action\Validator\ModifierPreventAction;
use Mush\Action\Validator\PlayerAlive;
use Mush\Action\Validator\PlayerCanAffordPoints;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractAction
{
    protected ActionConfig $actionConfig;
    protected Player $player;
    protected ?LogParameterInterface $target = null;
    protected ?array $parameters = [];
    protected ActionProviderInterface $actionProvider;

    protected ActionEnum $name;

    protected EventServiceInterface $eventService;
    protected ActionServiceInterface $actionService;
    protected ValidatorInterface $validator;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator
    ) {
        $this->eventService = $eventService;
        $this->actionService = $actionService;
        $this->validator = $validator;
    }

    public function loadParameters(
        ActionConfig $actionConfig,
        ActionProviderInterface $actionProvider,
        Player $player,
        ?LogParameterInterface $target = null,
        array $parameters = []
    ): void {
        if (!$this->support($target, $parameters)) {
            $parameters = [];
            $parameters['parameters'] = json_encode($parameters);

            $actionName = $actionConfig->getActionName()->value;

            throw new \InvalidArgumentException("Action {$actionName} does not support the target {$target?->getLogName()}, or one of the parameters is missing in {$parameters['parameters']}.");
        }

        $this->actionConfig = $actionConfig;
        $this->actionProvider = $actionProvider;
        $this->player = $player;
        $this->target = $target;
        $this->parameters = $parameters;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new PlayerAlive(['groups' => ['visibility']]));
        $metadata->addConstraint(new HasAction(['groups' => ['visibility']]));
        $metadata->addConstraint(new PlayerCanAffordPoints(['groups' => ['execute']]));
        $metadata->addConstraint(new ModifierPreventAction(['groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::SYMPTOMS_ARE_PREVENTING_ACTION]));
        $metadata->addConstraint(new IsActionProviderOperational(['groups' => ['execute']]));
        $metadata->addConstraint(new AdminAction(['groups' => [ClassConstraint::VISIBILITY]]));
    }

    public function isVisible(): bool
    {
        $validator = $this->validator;

        return $validator->validate($this, null, 'visibility')->count() === 0;
    }

    public function cannotExecuteReason(): ?string
    {
        $validator = $this->validator;

        $executeViolations = $validator->validate($this, null, 'execute');
        $visibilityViolations = $validator->validate($this, null, 'visibility');

        /** @var ConstraintViolationInterface $violation */
        foreach ($executeViolations as $violation) {
            return (string) $violation->getMessage();
        }

        /** @var ConstraintViolationInterface $violation */
        foreach ($visibilityViolations as $violation) {
            return (string) $violation->getMessage();
        }

        return null;
    }

    public function execute(): ActionResult
    {
        if ($reason = $this->cannotExecuteReason()) {
            return new Error($reason);
        }

        $result = $this->checkResult();
        $result->setVisibility($this->actionConfig->getVisibility($result->getName()));

        $preActionEvent = new ActionEvent(
            actionConfig: $this->actionConfig,
            actionProvider: $this->actionProvider,
            player: $this->player,
            tags: $this->getTags(),
            actionTarget: $this->target
        );
        $preActionEvent->setActionResult($result);
        $this->eventService->callEvent($preActionEvent, ActionEvent::PRE_ACTION);

        $this->actionService->applyCostToPlayer(
            $this->player,
            $this->actionConfig,
            $this->actionProvider,
            $this->target,
            $result,
            $this->getTags()
        );

        $resultActionEvent = new ActionEvent(
            actionConfig: $this->actionConfig,
            actionProvider: $this->actionProvider,
            player: $this->player,
            tags: $this->getTags(),
            actionTarget: $this->target
        );
        $resultActionEvent->setActionResult($result);
        $this->eventService->callEvent($resultActionEvent, ActionEvent::RESULT_ACTION);

        $this->applyEffect($result);

        $postActionEvent = new ActionEvent(
            actionConfig: $this->actionConfig,
            actionProvider: $this->actionProvider,
            player: $this->player,
            tags: $this->getTags(),
            actionTarget: $this->target
        );
        if ($this->getActionPointCost() === 0) {
            $postActionEvent->addTag(ActionTypeEnum::ACTION_ZERO_ACTION_COST->value);
        }

        $postActionEvent->setActionResult($result);

        $this->eventService->callEvent($postActionEvent, ActionEvent::POST_ACTION);

        return $result;
    }

    public function getActionName(): string
    {
        return $this->name->value;
    }

    public function getActionProvider(): ActionProviderInterface
    {
        return $this->actionProvider;
    }

    public function getActionPointCost(): int
    {
        return $this->actionService->getActionModifiedActionVariable(
            $this->player,
            $this->actionConfig,
            $this->actionProvider,
            $this->target,
            PlayerVariableEnum::ACTION_POINT,
            $this->getTags()
        );
    }

    public function getMovementPointCost(): int
    {
        return $this->actionService->getActionModifiedActionVariable(
            $this->player,
            $this->actionConfig,
            $this->actionProvider,
            $this->target,
            PlayerVariableEnum::MOVEMENT_POINT,
            $this->getTags()
        );
    }

    public function getMoralPointCost(): int
    {
        return $this->actionService->getActionModifiedActionVariable(
            $this->player,
            $this->actionConfig,
            $this->actionProvider,
            $this->target,
            PlayerVariableEnum::MORAL_POINT,
            $this->getTags()
        );
    }

    public function getOutputQuantity(): int
    {
        return $this->actionService->getActionModifiedActionVariable(
            $this->player,
            $this->actionConfig,
            $this->actionProvider,
            $this->target,
            ActionVariableEnum::OUTPUT_QUANTITY,
            $this->getTags()
        );
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getParameters(): ?array
    {
        return $this->parameters;
    }

    public function getTarget(): ?LogParameterInterface
    {
        return $this->target;
    }

    public function playerTarget(): Player
    {
        return $this->target instanceof Player ? $this->target : throw new \InvalidArgumentException('Target is not a player.');
    }

    public function gameEquipmentTarget(): GameEquipment
    {
        return $this->target instanceof GameEquipment ? $this->target : throw new \InvalidArgumentException('Target is not a GameEquipment.');
    }

    public function getActionConfig(): ActionConfig
    {
        return $this->actionConfig;
    }

    public function getTags(): array
    {
        return $this->actionConfig->getActionTags();
    }

    public function hasTag(string $tag): bool
    {
        return \in_array($tag, $this->getTags(), true);
    }

    public function isNotAdminAction(): bool
    {
        return \in_array(ActionTypeEnum::ACTION_ADMIN, $this->getActionConfig()->getTypes(), true) === false;
    }

    abstract public function support(?LogParameterInterface $target, array $parameters): bool;

    abstract protected function checkResult(): ActionResult;

    abstract protected function applyEffect(ActionResult $result): void;
}
