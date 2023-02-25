<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Validator\Mechanic;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Book;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class ReadBook extends AbstractAction
{
    protected string $name = ActionEnum::READ_BOOK;

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter instanceof GameItem;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new Mechanic(['mechanic' => EquipmentMechanicEnum::BOOK, 'groups' => ['visibility']]));
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        /** @var GameItem $parameter */
        $parameter = $this->parameter;

        /** @var Book $bookType */
        $bookType = $parameter->getEquipment()->getMechanicByName(EquipmentMechanicEnum::BOOK);
        $this->player->addSkill($bookType->getSkill());

        $equipmentEvent = new InteractWithEquipmentEvent(
            $parameter,
            $this->player,
            VisibilityEnum::HIDDEN,
            $this->getAction()->getActionTags(),
            new \DateTime(),
        );
        $this->eventService->callEvent($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);
    }
}
