<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Validator\FullHull;
use Mush\Action\Validator\Reach;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Daedalus\Event\DaedalusModifierEvent;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\AbstractQuantityEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class StrengthenHull extends AttemptAction
{
    protected string $name = ActionEnum::STRENGTHEN_HULL;

    private const BASE_REPAIR = 5;

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter instanceof GameItem;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new FullHull(['groups' => ['execute']]));
    }

    protected function applyEffect(ActionResult $result): void
    {
        /** @var GameItem $parameter */
        $parameter = $this->parameter;
        $time = new \DateTime();

        if ($result instanceof Success) {
            $quantity = self::BASE_REPAIR;

            $daedalusEvent = new DaedalusModifierEvent(
                $this->player->getDaedalus(),
                DaedalusVariableEnum::HULL,
                $quantity,
                $this->getActionName(),
                $time
            );

            $daedalusEvent->setPlayer($this->player);
            $this->eventDispatcher->dispatch($daedalusEvent, AbstractQuantityEvent::CHANGE_VARIABLE);

            $equipmentEvent = new InteractWithEquipmentEvent(
                $parameter,
                $this->player,
                VisibilityEnum::HIDDEN,
                $this->getActionName(),
                $time
            );
            $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);
        }
    }
}
