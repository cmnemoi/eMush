<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Error;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\ActionPoint;
use Mush\Action\Validator\AreSymptomsPreventingAction;
use Mush\Action\Validator\HasAction;
use Mush\Action\Validator\PlayerAlive;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Psr\Log\LoggerInterface;

abstract class AbstractAction
{
    protected Action $action;
    protected Player $player;

    protected ?LogParameterInterface $parameter = null;

    protected string $name;

    protected EventServiceInterface $eventService;
    protected ActionServiceInterface $actionService;
    protected ValidatorInterface $validator;
    protected LoggerInterface $logger;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        LoggerInterface $logger
    ) {
        $this->eventService = $eventService;
        $this->actionService = $actionService;
        $this->validator = $validator;
        $this->logger = $logger;
    }

    abstract protected function support(?LogParameterInterface $parameter): bool;

    public function loadParameters(Action $action, Player $player, ?LogParameterInterface $parameter = null): void
    {
        if (!$this->support($parameter)) {
            $className = isset($parameter) ? $parameter->getClassName() : '$parameter is null';
            throw new \InvalidArgumentException("Invalid action parameter, the parameter [{$className}] isn't supported.");
        }

        $this->action = $action;
        $this->player = $player;
        $this->parameter = $parameter;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new PlayerAlive(['groups' => ['visibility']]));
        $metadata->addConstraint(new HasAction(['groups' => ['visibility']]));
        $metadata->addConstraint(new ActionPoint(['groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::INSUFFICIENT_ACTION_POINT]));
        $metadata->addConstraint(new AreSymptomsPreventingAction(['groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::SYMPTOMS_ARE_PREVENTING_ACTION]));
    }

    public function isVisible(): bool
    {
        $validator = $this->validator;

        return $validator->validate($this, null, 'visibility')->count() === 0;
    }

    public function cannotExecuteReason(): ?string
    {
        $validator = $this->validator;
        $violations = $validator->validate($this, null, 'execute');

        /** @var ConstraintViolationInterface $violation */
        foreach ($violations as $violation) {
            return (string) $violation->getMessage();
        }

        return null;
    }

    abstract protected function checkResult(): ActionResult;

    abstract protected function applyEffect(ActionResult $result): void;

    public function execute(): ActionResult
    {
        if (!$this->isVisible() ||
            $this->cannotExecuteReason() !== null
        ) { 
            $errorMessage = 'AbstractAction::execute() - Cannot execute action';
            $this->logger->error($errorMessage);
            return new Error($errorMessage);
        }

        $parameter = $this->getParameter();

        $preActionEvent = new ActionEvent($this->action, $this->player, $parameter);
        $this->eventService->callEvent($preActionEvent, ActionEvent::PRE_ACTION);

        $this->actionService->applyCostToPlayer($this->player, $this->action, $this->parameter);

        $result = $this->checkResult();

        $result->setVisibility($this->action->getVisibility($result->getName()));

        $resultActionEvent = new ActionEvent($this->action, $this->player, $parameter);
        $resultActionEvent->setActionResult($result);
        $this->eventService->callEvent($resultActionEvent, ActionEvent::RESULT_ACTION);

        $this->applyEffect($result);

        $postActionEvent = new ActionEvent($this->action, $this->player, $parameter);
        $postActionEvent->setActionResult($result);
        $this->eventService->callEvent($postActionEvent, ActionEvent::POST_ACTION);

        return $result;
    }

    public function getActionName(): string
    {
        return $this->name;
    }

    public function getActionPointCost(): int
    {
        return $this->actionService->getTotalActionPointCost($this->player, $this->action, $this->parameter);
    }

    public function getMovementPointCost(): int
    {
        return $this->actionService->getTotalMovementPointCost($this->player, $this->action, $this->parameter);
    }

    public function getMoralPointCost(): int
    {
        return $this->actionService->getTotalMoralPointCost($this->player, $this->action, $this->parameter);
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getParameter(): ?LogParameterInterface
    {
        return $this->parameter;
    }

    public function getAction(): Action
    {
        return $this->action;
    }
}
