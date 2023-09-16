<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Fail;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Validator\AreShowersDismantled;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ReachEnum;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class WashInSink extends AbstractAction
{
    protected string $name = ActionEnum::WASH_IN_SINK;

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter instanceof GameEquipment;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach([
            'reach' => ReachEnum::ROOM,
            'groups' => ['visibility'],
        ]));
        $metadata->addConstraint(new AreShowersDismantled([
            'groups' => ['visibility'],
        ]));
        $metadata->addConstraint(new HasStatus([
            'status' => PlayerStatusEnum::ALREADY_WASHED_IN_THE_SINK,
            'target' => HasStatus::PLAYER,
            'contain' => false,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::ALREADY_WASHED_IN_SINK_TODAY,
        ]));
        $metadata->addConstraint(new HasStatus([
            'status' => EquipmentStatusEnum::BROKEN,
            'contain' => false,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::BROKEN_EQUIPMENT,
        ]));
    }

    protected function checkResult(): ActionResult
    {
        if ($this->player->getStatusByName(PlayerStatusEnum::MUSH)) {
            return new Fail();
        }

        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        if ($dirty = $this->player->getStatusByName(PlayerStatusEnum::DIRTY)) {
            $this->player->removeStatus($dirty);
        }

        $statusEvent = new StatusEvent(
            PlayerStatusEnum::ALREADY_WASHED_IN_THE_SINK,
            $this->player,
            $this->getAction()->getActionTags(),
            new \DateTime(),
        );
        $this->eventService->callEvent($statusEvent, StatusEvent::STATUS_APPLIED);
    }
}
