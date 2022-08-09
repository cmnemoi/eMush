<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Validator\Cookable;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class ExpressCook extends AbstractAction
{
    protected string $name = ActionEnum::EXPRESS_COOK;

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter instanceof GameEquipment;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new Cookable(['groups' => ['visibility']]));
    }

    protected function applyEffects(): ActionResult
    {
        /** @var GameEquipment $parameter */
        $parameter = $this->parameter;

        if ($parameter->getEquipment()->getName() === GameRationEnum::STANDARD_RATION) {
            $equipmentEvent = new EquipmentEvent(
                GameRationEnum::COOKED_RATION,
                $this->player,
                VisibilityEnum::PUBLIC,
                $this->getActionName(),
                new \DateTime()
            );
            $equipmentEvent->setExistingEquipment($parameter);
            $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_TRANSFORM);
        } elseif ($parameter->getStatusByName(EquipmentStatusEnum::FROZEN)) {
            $statusEvent = new StatusEvent(
                EquipmentStatusEnum::FROZEN,
                $parameter,
                $this->getActionName(),
                new \DateTime()
            );

            $this->eventDispatcher->dispatch($statusEvent, StatusEvent::STATUS_REMOVED);
        }

        // @TODO add effect on the link with sol

        return new Success();
    }
}
