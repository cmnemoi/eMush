<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\CriticalSuccess;
use Mush\Action\ActionResult\Fail;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Event\ApplyEffectEvent;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasDiseases;
use Mush\Action\Validator\HasStatus;
use Mush\Disease\Enum\TypeEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Enum\ActionOutputEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Implement self surgery action
 * For 4 Action Points, a player lying down in the medlab can heal one of its injury
 * There is a chance to fail and get a septis
 * There is a chance for a critical success that grant the player extra triumph.
 *
 * More info : http://mushpedia.com/wiki/Surgery_Pod
 */
class SelfSurgery extends AbstractSurgery
{
    protected string $name = ActionEnum::SELF_SURGERY;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        RandomServiceInterface $randomService
    ) {
        parent::__construct(
            $eventService,
            $actionService,
            $validator,
            $randomService
        );
    }

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter instanceof GameEquipment;
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
            'type' => TypeEnum::INJURY,
            'message' => ActionImpossibleCauseEnum::HEAL_NO_INJURY,
        ]));
        $metadata->addConstraint(new HasStatus([
            'status' => EquipmentStatusEnum::BROKEN,
            'contain' => false,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::BROKEN_EQUIPMENT,
        ]));
    }

    public function getFailChance(): int
    {
        return 10;
    }

    public function getCriticalSuccessChance(): int
    {
        return 5;
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

    private function successSurgery(string $reason): void
    {
        $diseaseEvent = new ApplyEffectEvent(
            $this->player,
            $this->player,
            VisibilityEnum::PUBLIC,
            $this->getActionName() . '_' . $reason,
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
            ActionEnum::SURGERY,
            new \DateTime()
        );
        $this->eventService->callEvent($diseaseEvent, ApplyEffectEvent::PLAYER_GET_SICK);
    }
}
