<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Validator\HasEquipment;
use Mush\Action\Validator\HasStatus as StatusValidator;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * implement gag action.
 * For 1 Action Points, a player holding duct tape can gag another player
 *  - target player get the gagged status
 *  - target player can ungag for 1 pa.
 *
 * More info: http://mushpedia.com/wiki/Duct_Tape
 */
class Gag extends AbstractAction
{
    protected string $name = ActionEnum::GAG;

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter instanceof Player;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new StatusValidator([
            'status' => PlayerStatusEnum::GAGGED,
            'target' => StatusValidator::PARAMETER,
            'contain' => false,
            'groups' => ['visibility'],
        ]));
        $metadata->addConstraint(new HasEquipment([
            'reach' => ReachEnum::INVENTORY,
            'equipments' => [ToolItemEnum::DUCT_TAPE],
            'contains' => true,
            'checkIfOperational' => true,
            'target' => HasEquipment::PLAYER,
            'groups' => ['visibility'],
        ]));
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        /** @var Player $parameter */
        $parameter = $this->parameter;

        $statusEvent = new StatusEvent(
            PlayerStatusEnum::GAGGED,
            $parameter,
            $this->getAction()->getActionTags(),
            new \DateTime(),
        );

        $this->eventService->callEvent($statusEvent, StatusEvent::STATUS_APPLIED);
    }
}
