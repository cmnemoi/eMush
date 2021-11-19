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
use Mush\Game\Event\AbstractQuantityEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
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

    protected function applyEffects(): ActionResult
    {
        /** @var GameItem $parameter */
        $parameter = $this->parameter;

        $response = $this->makeAttempt();

        if ($response instanceof Success) {
            $quantity = self::BASE_REPAIR;

            $daedalusEvent = new DaedalusModifierEvent(
                $this->player->getDaedalus(),
                DaedalusVariableEnum::HULL,
                $quantity,
                $this->getActionName(),
                new \DateTime()
            );
            $daedalusEvent->setPlayer($this->player);
            $this->eventDispatcher->dispatch($daedalusEvent, AbstractQuantityEvent::CHANGE_VARIABLE);

            $equipmentEvent = new EquipmentEvent(
                $parameter->getName(),
                $this->player,
                VisibilityEnum::HIDDEN,
                $this->getActionName(),
                new \DateTime()
            );
            $equipmentEvent->setExistingEquipment($parameter);
            $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);
        }

        return $response;
    }
}
