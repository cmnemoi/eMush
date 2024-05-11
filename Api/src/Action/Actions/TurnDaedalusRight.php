<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Validator\HasEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ReachEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;

final class TurnDaedalusRight extends AbstractTurnDaedalusAction
{
    protected ActionEnum $name = ActionEnum::TURN_DAEDALUS_RIGHT;

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new HasEquipment([
            'reach' => ReachEnum::DAEDALUS,
            'equipments' => [EquipmentEnum::REACTOR_LATERAL_BRAVO],
            'checkIfOperational' => true,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::BRAVO_REACTOR_BROKEN,
        ]));
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $this->daedalusTravelService->turnDaedalusRight(
            daedalus: $this->player->getDaedalus(),
            reasons: $this->actionConfig->getActionTags()
        );
    }
}
