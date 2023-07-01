<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Validator\HasEquipment;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\IsPatrolShipDamaged;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\Validator\Mapping\ClassMetadata;

final class Renovate extends AttemptAction
{
    protected string $name = ActionEnum::RENOVATE;

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter instanceof GameEquipment;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new HasEquipment([
            'reach' => ReachEnum::ROOM, 
            'equipments' => [ItemEnum::METAL_SCRAPS],
            'groups' => ['visibility']]
        ));
        $metadata->addConstraint(new IsPatrolShipDamaged(['groups' => ['visibility']]));
    }

    protected function applyEffect(ActionResult $result): void
    {
        /** @var GameEquipment $parameter */
        $parameter = $this->parameter;

        if ($result instanceof Success) {
            $statusEvent = new StatusEvent(
                EquipmentStatusEnum::BROKEN,
                $parameter,
                $this->getAction()->getActionTags(),
                new \DateTime()
            );
            $this->eventService->callEvent($statusEvent, StatusEvent::STATUS_REMOVED);
        }
    }
}
