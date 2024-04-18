<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Validator\GameVariableLevel;
use Mush\Action\Validator\PlaceType;
use Mush\Action\Validator\Reach;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Daedalus\Event\DaedalusVariableEvent;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class StrengthenHull extends AttemptAction
{
    protected string $name = ActionEnum::STRENGTHEN_HULL;

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new GameVariableLevel([
            'target' => GameVariableLevel::DAEDALUS,
            'variableName' => DaedalusVariableEnum::HULL,
            'checkMode' => GameVariableLevel::IS_MAX,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::DAEDALUS_ALREADY_FULL_HULL,
        ]));
        $metadata->addConstraint(new PlaceType(['groups' => ['visible'], 'type' => 'room']));
    }

    protected function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameItem;
    }

    protected function applyEffect(ActionResult $result): void
    {
        /** @var GameItem $target */
        $target = $this->target;
        $time = new \DateTime();

        if ($result instanceof Success) {
            $quantity = $this->getOutputQuantity();

            $daedalusEvent = new DaedalusVariableEvent(
                $this->player->getDaedalus(),
                DaedalusVariableEnum::HULL,
                $quantity,
                $this->getAction()->getActionTags(),
                $time
            );

            $daedalusEvent->setAuthor($this->player);
            $this->eventService->callEvent($daedalusEvent, VariableEventInterface::CHANGE_VARIABLE);

            $equipmentEvent = new InteractWithEquipmentEvent(
                $target,
                $this->player,
                VisibilityEnum::HIDDEN,
                $this->getAction()->getActionTags(),
                $time
            );
            $this->eventService->callEvent($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);
        }
    }
}
