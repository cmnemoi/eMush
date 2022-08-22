<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\CriticalSuccess;
use Mush\Action\ActionResult\Error;
use Mush\Action\ActionResult\Fail;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Event\ApplyEffectEvent;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\AreMedicalSuppliesOnReach;
use Mush\Action\Validator\HasDiseases;
use Mush\Action\Validator\HasEquipment;
use Mush\Action\Validator\HasStatus;
use Mush\Disease\Enum\TypeEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Game\Enum\ActionOutputEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Enum\ModifierTargetEnum;
use Mush\Modifier\Service\ModifierServiceInterface;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Implement surgery action
 * A medic can perform a surgery in medlab or if it holds the medikit
 * For 2 Action Points, medic can heal one injury of a player that is lying down
 * There is a chance to fail and give a septis
 * There is a chance for a critical success that grant the player extra triumph.
 *
 * More info : http://mushpedia.com/wiki/Medic
 */
class Surgery extends AbstractAction
{
    protected string $name = ActionEnum::SURGERY;

    private const FAIL_CHANCES = 10;
    private const CRITICAL_SUCCESS_CHANCES = 15;

    private RandomServiceInterface $randomService;
    private ModifierServiceInterface $modifierService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        RandomServiceInterface $randomService,
        ModifierServiceInterface $modifierService
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService,
            $validator
        );

        $this->randomService = $randomService;
        $this->modifierService = $modifierService;
    }

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter instanceof Player;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new HasStatus([
            'status' => PlayerStatusEnum::LYING_DOWN,
            'target' => HasStatus::PARAMETER,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::SURGERY_NOT_LYING_DOWN,
        ]));
        $metadata->addConstraint(new AreMedicalSuppliesOnReach([
            'groups' => ['visibility'],
        ]));
        $metadata->addConstraint(new HasDiseases([
            'groups' => ['visibility'],
            'target' => HasDiseases::PARAMETER,
            'isEmpty' => false,
            'type' => TypeEnum::INJURY,
        ]));
        $metadata->addConstraint(new HasEquipment([
            'groups' => ['visibility'],
            'equipment' => ToolItemEnum::MEDIKIT,
            'reach' => ReachEnum::INVENTORY,
            'contains' => true,
        ]));
    }

    protected function applyEffects(): ActionResult
    {
        /** @var Player $targetPlayer */
        $targetPlayer = $this->parameter;

        $date = new \DateTime();

        $failChances = $this->modifierService->getEventModifiedValue(
            $this->player,
            [ActionEnum::SURGERY],
            ModifierTargetEnum::PERCENTAGE,
            self::FAIL_CHANCES,
            $this->getActionName(),
            $date,
        );
        $criticalSuccessChances = $this->modifierService->getEventModifiedValue(
            $this->player,
            [ActionEnum::SURGERY],
            ModifierTargetEnum::CRITICAL_PERCENTAGE,
            self::CRITICAL_SUCCESS_CHANCES,
            $this->getActionName(),
            $date,
        );

        $result = $this->randomService->outputCriticalChances($failChances, 0, $criticalSuccessChances);

        if ($result === ActionOutputEnum::FAIL) {
            return $this->failedSurgery($targetPlayer, $date);
        }

        $this->successSurgery($targetPlayer, $result, $date);

        if ($result === ActionOutputEnum::CRITICAL_SUCCESS) {
            return new CriticalSuccess();
        } elseif ($result === ActionOutputEnum::SUCCESS) {
            return new Success();
        }

        return new Error('this output should not exist');
    }

    private function successSurgery(Player $targetPlayer, string $reason, \DateTime $time): void
    {
        $diseaseEvent = new ApplyEffectEvent(
            $this->player,
            $targetPlayer,
            VisibilityEnum::PUBLIC,
            $this->getActionName() . '_' . $reason,
            $time
        );

        $this->eventDispatcher->dispatch($diseaseEvent, ApplyEffectEvent::PLAYER_CURE_INJURY);
    }

    private function failedSurgery(Player $targetPlayer, \DateTime $time): ActionResult
    {
        $diseaseEvent = new ApplyEffectEvent(
            $this->player,
            $targetPlayer,
            VisibilityEnum::PUBLIC,
            $this->getActionName(),
            $time
        );
        $this->eventDispatcher->dispatch($diseaseEvent, ApplyEffectEvent::PLAYER_GET_SICK);

        return new Fail();
    }
}
