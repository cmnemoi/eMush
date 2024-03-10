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

final class TurnDaedalusLeft extends AbstractTurnDaedalusAction
{
    protected string $name = ActionEnum::TURN_DAEDALUS_LEFT;

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new HasEquipment([
            'reach' => ReachEnum::DAEDALUS,
            'equipments' => [EquipmentEnum::REACTOR_LATERAL_ALPHA],
            'checkIfOperational' => true,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::ALPHA_REACTOR_BROKEN,
        ]));
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $this->daedalusTravelService->turnDaedalusLeft(
            daedalus: $this->player->getDaedalus(),
            reasons: $this->action->getActionTags()
        );
    }
}
