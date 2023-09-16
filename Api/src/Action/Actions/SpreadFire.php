<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\PlaceType;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class SpreadFire extends AbstractAction
{
    protected string $name = ActionEnum::SPREAD_FIRE;

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter === null;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new HasStatus(['status' => PlayerStatusEnum::MUSH, 'target' => HasStatus::PLAYER, 'groups' => ['visibility']]));
        $metadata->addConstraint(new PlaceType(['groups' => ['execute'], 'type' => 'room', 'message' => ActionImpossibleCauseEnum::NOT_A_ROOM]));
        $metadata->addConstraint(new HasStatus(['status' => StatusEnum::FIRE, 'target' => HasStatus::PLAYER_ROOM, 'contain' => false, 'groups' => ['visibility']]));
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $statusEvent = new StatusEvent(
            StatusEnum::FIRE,
            $this->player->getPlace(),
            $this->getAction()->getActionTags(),
            new \DateTime(),
        );
        $this->eventService->callEvent($statusEvent, StatusEvent::STATUS_APPLIED);
    }
}
