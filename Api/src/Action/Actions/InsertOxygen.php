<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Validator\Oxygen;
use Mush\Action\Validator\ParameterName;
use Mush\Action\Validator\Reach;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Daedalus\Event\DaedalusModifierEvent;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\AbstractQuantityEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class InsertOxygen extends AbstractAction
{
    protected string $name = ActionEnum::INSERT_OXYGEN;

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter instanceof GameItem;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new ParameterName(['name' => ItemEnum::OXYGEN_CAPSULE, 'groups' => ['visibility']]));
        $metadata->addConstraint(new Oxygen(['retrieve' => false, 'groups' => ['visibility']]));
    }

    protected function applyEffects(): ActionResult
    {
        /** @var GameItem $parameter */
        $parameter = $this->parameter;
        $time = new \DateTime();

        // Delete the oxygen
        $equipmentEvent = new InteractWithEquipmentEvent(
            $parameter,
            $this->player,
            VisibilityEnum::HIDDEN,
            $this->getActionName(),
            $time
        );
        $this->eventService->callEvent($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);

        // Add Oxygen
        $daedalusEvent = new DaedalusModifierEvent(
            $this->player->getDaedalus(),
            DaedalusVariableEnum::OXYGEN,
            1,
            $this->getActionName(),
            $time
        );
        $this->eventService->callEvent($daedalusEvent, AbstractQuantityEvent::CHANGE_VARIABLE);

        return new Success();
    }
}
