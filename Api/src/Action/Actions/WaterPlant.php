<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Validator\PlantWaterable;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ReachEnum;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class WaterPlant extends AbstractAction
{
    protected string $name = ActionEnum::WATER_PLANT;

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter instanceof GameItem;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new PlantWaterable(['groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::WATER_PLANT_NO_THIRSTY]));
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        /** @var GameItem $parameter */
        $parameter = $this->parameter;

        /** @var Status $status */
        $status = ($parameter->getStatusByName(EquipmentStatusEnum::PLANT_THIRSTY)
            ?? $parameter->getStatusByName(EquipmentStatusEnum::PLANT_DRY));

        $statusEvent = new StatusEvent(
            $status->getName(),
            $parameter,
            $this->getAction()->getActionTags(),
            new \DateTime(),
        );
        $this->eventService->callEvent($statusEvent, StatusEvent::STATUS_REMOVED);
    }
}
