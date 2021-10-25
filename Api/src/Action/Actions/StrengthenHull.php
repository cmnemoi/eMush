<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Validator\FullHull;
use Mush\Action\Validator\Reach;
use Mush\Daedalus\Event\DaedalusModifierEvent;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
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

        $parameter->setHolder(null);

        $response = $this->makeAttempt();

        if ($response instanceof Success) {
            $quantity = self::BASE_REPAIR;

            $daedalusEvent = new DaedalusModifierEvent(
                $this->player->getDaedalus(),
                $quantity,
                $this->getActionName(),
                new \DateTime()
            );
            $this->eventDispatcher->dispatch($daedalusEvent, DaedalusModifierEvent::CHANGE_HULL);

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
