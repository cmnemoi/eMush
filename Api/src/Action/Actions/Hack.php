<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Fail;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Validator\HasEquipment;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\Validator\Mapping\ClassMetadata;

final class Hack extends AttemptAction
{
    protected string $name = ActionEnum::HACK;

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new HasStatus([
            'status' => EquipmentStatusEnum::BROKEN,
            'contain' => false,
            'target' => HasStatus::PARAMETER,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::BROKEN_EQUIPMENT,
        ]));
        $metadata->addConstraint(new HasEquipment([
            'reach' => ReachEnum::INVENTORY,
            'equipments' => [ToolItemEnum::HACKER_KIT],
            'checkIfOperational' => true,
            'groups' => ['visibility'],
        ]));
    }

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter instanceof GameEquipment;
    }

    protected function applyEffect(ActionResult $result): void
    {
        if ($result instanceof Fail) {
            return;
        }

        /** @var GameEquipment $terminalToHack */
        $terminalToHack = $this->parameter;

        $statusEvent = new StatusEvent(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->player,
            tags: $this->getAction()->getActionTags(),
            time: new \DateTime(),
        );
        $statusEvent->setStatusTarget($terminalToHack);
        $this->eventService->callEvent($statusEvent, StatusEvent::STATUS_APPLIED);
    }
}
