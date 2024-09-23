<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Validator\ClassConstraint;
use Mush\Action\Validator\HasStatus;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;

final class UpgradeDroneToTurbo extends AbstractUpgradeDrone
{
    protected ActionEnum $name = ActionEnum::UPGRADE_DRONE_TO_TURBO;

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new HasStatus([
            'status' => EquipmentStatusEnum::TURBO_DRONE_UPGRADE,
            'target' => HasStatus::PARAMETER,
            'contain' => false,
            'groups' => [ClassConstraint::VISIBILITY],
        ]));
    }
}
