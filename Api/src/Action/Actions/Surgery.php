<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\CriticalSuccess;
use Mush\Action\Entity\ActionResult\Error;
use Mush\Action\Entity\ActionResult\Fail;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Action\Event\ActionVariableEvent;
use Mush\Action\Event\ApplyEffectEvent;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\ClassConstraint;
use Mush\Action\Validator\HasDiseases;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\MedicalSuppliesOnReach;
use Mush\Action\Validator\Reach;
use Mush\Disease\Enum\MedicalConditionTypeEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Enum\ActionOutputEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Implement surgery action
 * A medic can perform a surgery in medlab or if it holds the medikit
 * For 2 ActionConfig Points, medic can heal one injury of a player that is lying down
 * There is a chance to fail and give a septis
 * There is a chance for a critical success that grant the player extra triumph.
 *
 * More info : http://mushpedia.com/wiki/Medic
 */
final class Surgery extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::SURGERY;

    private RandomServiceInterface $randomService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        RandomServiceInterface $randomService,
    ) {
        parent::__construct(
            $eventService,
            $actionService,
            $validator
        );

        $this->randomService = $randomService;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraints([
            new Reach([
                'reach' => ReachEnum::ROOM,
                'groups' => [ClassConstraint::VISIBILITY],
            ]),
            new MedicalSuppliesOnReach([
                'groups' => [ClassConstraint::VISIBILITY],
            ]),
            new HasDiseases([
                'groups' => [ClassConstraint::VISIBILITY],
                'target' => HasDiseases::PARAMETER,
                'isEmpty' => false,
                'type' => MedicalConditionTypeEnum::INJURY,
            ]),
            new HasStatus([
                'status' => PlayerStatusEnum::LYING_DOWN,
                'target' => HasStatus::PARAMETER,
                'groups' => [ClassConstraint::EXECUTE],
                'message' => ActionImpossibleCauseEnum::SURGERY_NOT_LYING_DOWN,
            ]),
        ]);
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof Player;
    }

    protected function checkResult(): ActionResult
    {
        $result = $this->randomService->outputCriticalChances(
            $this->getModifiedPercentage($this->failChances()),
            0,
            $this->getModifiedPercentage($this->criticalChances(), ActionVariableEnum::PERCENTAGE_CRITICAL)
        );

        if ($result === ActionOutputEnum::FAIL) {
            return new Fail();
        }
        if ($result === ActionOutputEnum::CRITICAL_SUCCESS) {
            return new CriticalSuccess();
        }
        if ($result === ActionOutputEnum::SUCCESS) {
            return new Success();
        }

        return new Error('this output should not exist');
    }

    protected function applyEffect(ActionResult $result): void
    {
        if ($result instanceof Fail) {
            $this->failedSurgery();
        } elseif ($result instanceof CriticalSuccess) {
            $this->criticalSuccessSurgery();
        } elseif ($result instanceof Success) {
            $this->successSurgery();
        }
    }

    private function criticalSuccessSurgery(): void
    {
        $diseaseEvent = new ApplyEffectEvent(
            $this->player,
            $this->playerTarget(),
            VisibilityEnum::PUBLIC,
            [$this->getActionName() . '_' . ActionOutputEnum::CRITICAL_SUCCESS],
            new \DateTime()
        );

        $this->eventService->callEvent($diseaseEvent, ApplyEffectEvent::PLAYER_CURE_INJURY);
    }

    private function successSurgery(): void
    {
        $diseaseEvent = new ApplyEffectEvent(
            $this->player,
            $this->playerTarget(),
            VisibilityEnum::PUBLIC,
            [$this->getActionName() . '_' . ActionOutputEnum::SUCCESS],
            new \DateTime()
        );

        $this->eventService->callEvent($diseaseEvent, ApplyEffectEvent::PLAYER_CURE_INJURY);
    }

    private function failedSurgery(): void
    {
        $diseaseEvent = new ApplyEffectEvent(
            $this->player,
            $this->playerTarget(),
            VisibilityEnum::PUBLIC,
            $this->getActionConfig()->getActionTags(),
            new \DateTime()
        );
        $this->eventService->callEvent($diseaseEvent, ApplyEffectEvent::PLAYER_GET_SICK);
    }

    private function getModifiedPercentage(int $percentage, string $mode = ActionVariableEnum::PERCENTAGE_SUCCESS): int
    {
        $criticalRollEvent = new ActionVariableEvent(
            actionConfig: $this->actionConfig,
            actionProvider: $this->actionProvider,
            variableName: $mode,
            quantity: $percentage,
            player: $this->player,
            tags: $this->getTags(),
            actionTarget: $this->playerTarget()
        );

        /** @var ActionVariableEvent $criticalRollEvent */
        $criticalRollEvent = $this->eventService->computeEventModifications($criticalRollEvent, ActionVariableEvent::ROLL_ACTION_PERCENTAGE);

        return $criticalRollEvent->getRoundedQuantity();
    }

    private function failChances(): int
    {
        return $this->getOutputQuantity();
    }

    private function criticalChances(): int
    {
        return $this->actionConfig->getCriticalRate();
    }
}
