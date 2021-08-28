<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameter;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Validator\HasStatus as StatusValidator;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class LieDown extends AbstractAction
{
    protected string $name = ActionEnum::LIE_DOWN;

    protected function support(?ActionParameter $parameter): bool
    {
        return $parameter instanceof GameEquipment;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new StatusValidator([
            'status' => PlayerStatusEnum::LYING_DOWN,
            'target' => StatusValidator::PLAYER,
            'contain' => false,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::ALREADY_IN_BED,
        ]));
        $metadata->addConstraint(new StatusValidator([
            'status' => EquipmentStatusEnum::BROKEN,
            'contain' => false,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::BROKEN_EQUIPMENT,
        ]));
        $metadata->addConstraint(new StatusValidator([
            'status' => PlayerStatusEnum::LYING_DOWN,
            'ownerSide' => false,
            'contain' => false,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::BED_OCCUPIED,
        ]));
    }

    protected function applyEffects(): ActionResult
    {
        /** @var GameEquipment $parameter */
        $parameter = $this->parameter;

        $statusEvent = new StatusEvent(PlayerStatusEnum::LYING_DOWN, $this->player, $this->getActionName(), new \DateTime());
        $statusEvent->setStatusTarget($parameter);

        $this->eventDispatcher->dispatch($statusEvent, StatusEvent::STATUS_APPLIED);

        return new Success();
    }
}
