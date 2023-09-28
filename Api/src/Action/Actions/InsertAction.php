<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Validator\Fuel;
use Mush\Daedalus\Event\DaedalusVariableEvent;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\RoomLog\Entity\LogParameterInterface;

abstract class InsertAction extends AbstractAction
{
    protected function support(?LogParameterInterface $support, array $parameters): bool
    {
        return $support instanceof GameItem;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        /** @var GameItem $toInsert */
        $toInsert = $this->getSupport();
        $time = new \DateTime();

        // Delete the fuel
        $equipmentEvent = new InteractWithEquipmentEvent(
            $toInsert,
            $this->player,
            VisibilityEnum::HIDDEN,
            $this->getAction()->getActionTags(),
            $time
        );
        $this->eventService->callEvent($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);

        // Add to container
        $daedalusEvent = new DaedalusVariableEvent(
            $this->player->getDaedalus(),
            $this->getDaedalusVariable(),
            1,
            $this->getAction()->getActionTags(),
            $time
        );
        $this->eventService->callEvent($daedalusEvent, VariableEventInterface::CHANGE_VARIABLE);
    }

    abstract protected function getDaedalusVariable(): string;
}
