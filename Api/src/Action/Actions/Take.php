<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Validator\InventoryFull;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class Take extends AbstractAction
{
    protected string $name = ActionEnum::TAKE;

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter instanceof GameItem;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::SHELVE, 'groups' => ['visibility']]));
        $metadata->addConstraint(new InventoryFull(['groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::FULL_INVENTORY]));
    }

    protected function applyEffects(): ActionResult
    {
        /** @var GameItem $parameter */
        $parameter = $this->parameter;

        $equipmentEvent = new InteractWithEquipmentEvent(
            $parameter,
            $this->player,
            VisibilityEnum::HIDDEN,
            $this->getActionName(),
            new \DateTime()
        );
        $this->eventService->callEvent($equipmentEvent, EquipmentEvent::CHANGE_HOLDER);

        return new Success();
    }
}
