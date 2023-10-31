<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Fail;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Class implementing the "Play arcade" action.
 * The Dynarcade is an equipment located in Alpha Bay 2.
 * It becomes available by purchasing the Purchased Gold Project Dynarcade in the Vending Machine.
 * Playing the arcade games gives you a chance to restore 2 Morale with 1 Action Point. But if you fail, you'll take some damage.
 *
 * More info : http://www.mushpedia.com/wiki/Dynarcade
 */
class PlayDynarcade extends AttemptAction
{
    protected string $name = ActionEnum::PLAY_ARCADE;

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new HasStatus([
            'status' => EquipmentStatusEnum::BROKEN,
            'contain' => false,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::BROKEN_EQUIPMENT,
        ]));
    }

    protected function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameEquipment;
    }

    protected function applyEffect(ActionResult $result): void
    {
        if ($result instanceof Success) {
            $playerModifierEvent = new PlayerVariableEvent(
                $this->player,
                PlayerVariableEnum::MORAL_POINT,
                $this->getOutputQuantity(),
                $this->getAction()->getActionTags(),
                new \DateTime()
            );

            $playerModifierEvent->setVisibility(VisibilityEnum::PRIVATE);
            $this->eventService->callEvent($playerModifierEvent, VariableEventInterface::CHANGE_VARIABLE);
        } elseif ($result instanceof Fail) {
            $playerModifierEvent = new PlayerVariableEvent(
                $this->player,
                PlayerVariableEnum::HEALTH_POINT,
                -1,
                $this->getAction()->getActionTags(),
                new \DateTime()
            );

            $playerModifierEvent->setVisibility(VisibilityEnum::PRIVATE);
            $this->eventService->callEvent($playerModifierEvent, VariableEventInterface::CHANGE_VARIABLE);
        }
    }
}
