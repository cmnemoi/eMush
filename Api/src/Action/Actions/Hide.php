<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\PlaceType;
use Mush\Action\Validator\PreMush;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\MoveEquipmentEvent;
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
        return $parameter instanceof GameItem;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new HasStatus(['status' => EquipmentStatusEnum::HIDDEN, 'contain' => false, 'groups' => ['visibility']]));
        $metadata->addConstraint(new PlaceType(['groups' => ['execute'], 'type' => 'room', 'message' => ActionImpossibleCauseEnum::NO_SHELVING_UNIT]));
        $metadata->addConstraint(new PreMush(['groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::PRE_MUSH_RESTRICTED]));
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        /** @var GameItem $parameter */
        $parameter = $this->parameter;
        $time = new \DateTime();

        $statusEvent = new StatusEvent(
            EquipmentStatusEnum::HIDDEN,
            $parameter,
            $this->getAction()->getActionTags(),
            $time
        );
        $statusEvent->setStatusTarget($this->player);
        $this->eventService->callEvent($statusEvent, StatusEvent::STATUS_APPLIED);

        if ($parameter->getHolder() instanceof Player) {
            $equipmentEvent = new MoveEquipmentEvent(
                $parameter,
                $this->player->getPlace(),
                $this->player,
                VisibilityEnum::HIDDEN,
                $this->getAction()->getActionTags(),
                $time
            );
            $this->eventService->callEvent($equipmentEvent, EquipmentEvent::CHANGE_HOLDER);
        }
    }
}
