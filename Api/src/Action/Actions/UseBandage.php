<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Validator\GameVariableLevel;
use Mush\Action\Validator\PlaceType;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class UseBandage extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::USE_BANDAGE;

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new GameVariableLevel([
            'target' => GameVariableLevel::PLAYER,
            'checkMode' => GameVariableLevel::IS_MAX,
            'variableName' => PlayerVariableEnum::HEALTH_POINT,
            'message' => ActionImpossibleCauseEnum::HEAL_NO_INJURY,
            'groups' => ['execute'],
        ]));
        $metadata->addConstraint(new PlaceType(['groups' => ['execute'], 'type' => 'planet', 'allowIfTypeMatches' => false, 'message' => ActionImpossibleCauseEnum::ON_PLANET]));
    }

    protected function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameEquipment;
    }

    protected function checkResult(): ActionResult
    {
        $healedQuantity = $this->getOutputQuantity();
        $success = new Success();

        return $success->setQuantity($healedQuantity);
    }

    protected function applyEffect(ActionResult $result): void
    {
        /** @var GameEquipment $target */
        $target = $this->target;
        $time = new \DateTime();
        $quantity = $this->getActionConfig()->getOutputQuantity();

        $playerVariableEvent = new PlayerVariableEvent(
            $this->player,
            PlayerVariableEnum::HEALTH_POINT,
            $quantity,
            $this->getActionConfig()->getActionTags(),
            $time
        );
        $playerVariableEvent->setVisibility(VisibilityEnum::HIDDEN);
        $this->eventService->callEvent($playerVariableEvent, VariableEventInterface::CHANGE_VARIABLE);

        // destroy the bandage
        $equipmentEvent = new InteractWithEquipmentEvent(
            $target,
            $this->player,
            VisibilityEnum::HIDDEN,
            $this->getActionConfig()->getActionTags(),
            $time
        );
        $this->eventService->callEvent($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);
    }
}
