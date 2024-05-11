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
use Mush\Action\Validator\HasDiseases;
use Mush\Action\Validator\HasStatus;
use Mush\Disease\Enum\MedicalConditionTypeEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Enum\ActionOutputEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Implement self surgery action
 * For 4 ActionConfig Points, a player lying down in the medlab can heal one of its injury
 * There is a chance to fail and get a septis
 * There is a chance for a critical success that grant the player extra triumph.
 *
 * More info : http://mushpedia.com/wiki/Surgery_Pod
 */
class SelfSurgery extends AbstractAction
{
    public const FAIL_CHANCES = 10;
    public const CRITICAL_SUCCESS_CHANCES = 5;
    protected ActionEnum $name = ActionEnum::SELF_SURGERY;

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
        $metadata->addConstraint(new HasStatus([
            'status' => PlayerStatusEnum::LYING_DOWN,
            'target' => HasStatus::PLAYER,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::SURGERY_NOT_LYING_DOWN,
        ]));
        $metadata->addConstraint(new HasDiseases([
            'groups' => ['execute'],
            'target' => HasDiseases::PLAYER,
            'isEmpty' => false,
            'type' => MedicalConditionTypeEnum::INJURY,
            'message' => ActionImpossibleCauseEnum::HEAL_NO_INJURY,
        ]));
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameEquipment;
    }

    protected function checkResult(): ActionResult
    {
        $result = $this->randomService->outputCriticalChances(
            $this->getModifiedPercentage(self::FAIL_CHANCES),
            0,
            $this->getModifiedPercentage(self::CRITICAL_SUCCESS_CHANCES, ActionVariableEnum::PERCENTAGE_CRITICAL)
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
            $this->successSurgery(ActionOutputEnum::CRITICAL_SUCCESS);
        } elseif ($result instanceof Success) {
            $this->successSurgery(ActionOutputEnum::SUCCESS);
        }
    }

    private function successSurgery(string $result): void
    {
        $diseaseEvent = new ApplyEffectEvent(
            $this->player,
            $this->player,
            VisibilityEnum::PUBLIC,
            [$this->getActionName() . '_' . $result],
            new \DateTime()
        );

        $this->eventService->callEvent($diseaseEvent, ApplyEffectEvent::PLAYER_CURE_INJURY);
    }

    private function failedSurgery(): void
    {
        $diseaseEvent = new ApplyEffectEvent(
            $this->player,
            $this->player,
            VisibilityEnum::PUBLIC,
            [ActionEnum::SURGERY],
            new \DateTime()
        );
        $this->eventService->callEvent($diseaseEvent, ApplyEffectEvent::PLAYER_GET_SICK);
    }

    private function getModifiedPercentage(int $percentage, string $mode = ActionVariableEnum::PERCENTAGE_SUCCESS): int
    {
        $criticalRollEvent = new ActionVariableEvent(
            $this->actionConfig,
            $this->actionProvider,
            $mode,
            $percentage,
            $this->player,
            $this->target
        );

        /** @var ActionVariableEvent $criticalRollEvent */
        $criticalRollEvent = $this->eventService->computeEventModifications($criticalRollEvent, ActionVariableEvent::ROLL_ACTION_PERCENTAGE);

        return $criticalRollEvent->getRoundedQuantity();
    }
}
