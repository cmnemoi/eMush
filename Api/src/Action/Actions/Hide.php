<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\IsRoom;
use Mush\Action\Validator\PreMush;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\Item;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class Hide extends AbstractAction
{
    protected string $name = ActionEnum::HIDE;

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter instanceof Item;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new HasStatus(['status' => EquipmentStatusEnum::HIDDEN, 'contain' => false, 'groups' => ['visibility']]));
        $metadata->addConstraint(new IsRoom(['groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::NO_SHELVING_UNIT]));
        $metadata->addConstraint(new PreMush(['groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::PRE_MUSH_RESTRICTED]));
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        /** @var Item $parameter */
        $parameter = $this->parameter;
        $time = new \DateTime();

        $statusEvent = new StatusEvent(
            EquipmentStatusEnum::HIDDEN,
            $parameter,
            $this->getActionName(),
            $time
        );
        $statusEvent->setStatusTarget($this->player);
        $this->eventDispatcher->dispatch($statusEvent, StatusEvent::STATUS_APPLIED);

        if ($parameter->getHolder() instanceof Player) {
            $equipmentEvent = new InteractWithEquipmentEvent(
                $parameter,
                $this->player,
                VisibilityEnum::HIDDEN,
                $this->getActionName(),
                $time
            );
            $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::CHANGE_HOLDER);
        }
    }
}
