<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ApplyEffectEvent;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\IsReported;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class ReportEquipment extends AbstractAction
{
    protected string $name = ActionEnum::REPORT_EQUIPMENT;

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter instanceof GameEquipment;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new HasStatus(['status' => EquipmentStatusEnum::BROKEN, 'groups' => ['visibility']]));
        $metadata->addConstraint(new IsReported(['groups' => ['visibility']]));
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        /** @var GameEquipment $parameter */
        $parameter = $this->parameter;

        $reportEvent = new ApplyEffectEvent(
            $this->player,
            $parameter,
            VisibilityEnum::PRIVATE,
            $this->getActionName(),
            new \DateTime()
        );

        $this->eventService->dispatch($reportEvent, ApplyEffectEvent::REPORT_EQUIPMENT);
    }
}
