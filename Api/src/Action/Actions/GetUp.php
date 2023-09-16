<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Validator\HasStatus;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class GetUp extends AbstractAction
{
    protected string $name = ActionEnum::GET_UP;

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter === null;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new HasStatus([
            'status' => PlayerStatusEnum::LYING_DOWN,
            'target' => HasStatus::PLAYER,
            'groups' => ['visibility'],
        ]));
    }

    protected function checkResult(): ActionResult
    {
        if (!$this->player->getStatusByName(PlayerStatusEnum::LYING_DOWN)) {
            throw new \LogicException('Player should have a lying down status');
        }

        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        if (!$lyingDownStatus = $this->player->getStatusByName(PlayerStatusEnum::LYING_DOWN)) {
            throw new \LogicException('Player should have a lying down status');
        }

        $statusEvent = new StatusEvent(
            $lyingDownStatus->getName(),
            $this->player,
            $this->getAction()->getActionTags(),
            new \DateTime(),
        );
        $statusEvent->setStatusTarget($lyingDownStatus->getTarget());
        $this->eventService->callEvent($statusEvent, StatusEvent::STATUS_REMOVED);
    }
}
