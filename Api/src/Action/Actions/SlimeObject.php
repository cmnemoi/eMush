<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\ClassConstraint;
use Mush\Action\Validator\HasSkill;
use Mush\Action\Validator\HasStatus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\GetRandomIntegerServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class SlimeObject extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::SLIME_OBJECT;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        private GetRandomIntegerServiceInterface $getRandomInteger,
        private StatusServiceInterface $statusService,
    ) {
        parent::__construct($eventService, $actionService, $validator);
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraints([
            new HasSkill([
                'skill' => SkillEnum::GREEN_JELLY,
                'groups' => [ClassConstraint::VISIBILITY],
            ]),
            new HasStatus([
                'status' => EquipmentStatusEnum::SLIMED,
                'target' => HasStatus::PARAMETER,
                'contain' => false,
                'groups' => [ClassConstraint::EXECUTE],
                'message' => ActionImpossibleCauseEnum::SLIME_ALREADY_DONE,
            ]),
        ]);
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameEquipment;
    }

    protected function checkResult(): ActionResult
    {
        $result = new Success();

        return $result->setQuantity($this->slimeDelay());
    }

    protected function applyEffect(ActionResult $result): void
    {
        $this->createSlimedStatus($result);
    }

    private function createSlimedStatus(ActionResult $actionResult): void
    {
        /** @var ChargeStatus $slimedStatus */
        $slimedStatus = $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::SLIMED,
            holder: $this->getTargetAsGameEquipment(),
            tags: $this->getTags(),
            time: new \DateTime(),
        );

        $this->statusService->updateCharge(
            chargeStatus: $slimedStatus,
            delta: $actionResult->getQuantityOrThrow(),
            tags: $this->getTags(),
            time: new \DateTime(),
            mode: VariableEventInterface::SET_VALUE,
        );
    }

    private function slimeDelay(): int
    {
        return $this->getRandomInteger->execute(min: 1, max: $this->getOutputQuantity());
    }
}
