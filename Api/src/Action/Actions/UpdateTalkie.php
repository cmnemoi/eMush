<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Validator\HasEquipment;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\PreMush;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class UpdateTalkie extends AbstractAction
{
    protected string $name = ActionEnum::UPDATE_TALKIE;

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter instanceof GameEquipment;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new PreMush(['groups' => ['visibility']]));
        $metadata->addConstraint(new HasEquipment([
            'reach' => ReachEnum::INVENTORY,
            'equipments' => [ItemEnum::TRACKER],
            'contains' => true,
            'checkIfOperational' => true,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::UPDATE_TALKIE_REQUIRE_TRACKER,
        ]));
        $metadata->addConstraint(new HasEquipment([
            'reach' => ReachEnum::ROOM,
            'equipment' => EquipmentEnum::NERON_CORE,
            'contains' => true,
            'checkIfOperational' => true,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::UPDATE_TALKIE_REQUIRE_NERON,
        ]));
        $metadata->addConstraint(new HasStatus([
            'status' => EquipmentStatusEnum::BROKEN, 'contain' => false, 'groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::BROKEN_EQUIPMENT,
        ]));
    }

    protected function applyEffects(): ActionResult
    {
        // destroy tracker
        /** @var GameItem $tracker */
        $tracker = $this->player->getEquipments()->filter(fn (GameItem $item) => $item->getName() === ItemEnum::TRACKER)->first();
        $equipmentEvent = new EquipmentEvent(
            $tracker->getName(),
            $this->player,
            VisibilityEnum::HIDDEN,
            $this->getActionName(),
            new \DateTime()
        );
        $equipmentEvent->setExistingEquipment($tracker);
        $this->eventService->callEvent($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);

        /** @var GameEquipment $parameter */
        $parameter = $this->parameter;

        $equipmentEvent = new EquipmentEvent(
            ItemEnum::ITRACKIE,
            $this->player,
            VisibilityEnum::PUBLIC,
            $this->getActionName(),
            new \DateTime()
        );
        $equipmentEvent->setExistingEquipment($parameter);
        $this->eventService->callEvent($equipmentEvent, EquipmentEvent::EQUIPMENT_TRANSFORM);

        return new Success();
    }
}
