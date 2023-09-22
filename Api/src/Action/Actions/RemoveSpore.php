<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Fail;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Validator\HasStatus;
use Mush\Equipment\Entity\GameItem;
use Mush\Game\Event\VariableEventInterface;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class RemoveSpore extends AbstractAction
{
    protected string $name = ActionEnum::REMOVE_SPORE;

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new HasStatus(['status' => PlayerStatusEnum::MUSH, 'target' => HasStatus::PLAYER, 'contain' => false, 'groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::MUSH_REMOVE_SPORE]));
        $metadata->addConstraint(new HasStatus(['status' => PlayerStatusEnum::IMMUNIZED, 'target' => HasStatus::PLAYER, 'contain' => false, 'groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::IMMUNIZED_REMOVE_SPORE]));
        $metadata->addConstraint(new HasStatus([
            'status' => EquipmentStatusEnum::BROKEN,
            'contain' => false,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::BROKEN_EQUIPMENT,
        ]));
    }

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter instanceof GameItem;
    }

    protected function checkResult(): ActionResult
    {
        $nbSpores = $this->player->getVariableValueByName(PlayerVariableEnum::SPORE);

        if ($nbSpores > 0) {
            return new Success();
        } else {
            return new Fail();
        }
    }

    protected function applyEffect(ActionResult $result): void
    {
        $playerModifierEvent = new PlayerVariableEvent(
            $this->player,
            PlayerVariableEnum::HEALTH_POINT,
            -3,
            $this->getAction()->getActionTags(),
            new \DateTime(),
        );

        $this->eventService->callEvent($playerModifierEvent, VariableEventInterface::CHANGE_VARIABLE);

        // The Player remove a spore
        $sporeLossEvent = new PlayerVariableEvent(
            $this->player,
            PlayerVariableEnum::SPORE,
            -1,
            $this->getAction()->getActionTags(),
            new \DateTime(),
        );
        $this->eventService->callEvent($sporeLossEvent, VariableEventInterface::CHANGE_VARIABLE);
    }
}
