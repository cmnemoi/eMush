<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Validator\ClassConstraint;
use Mush\Action\Validator\HasStatus;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;

final class UpgradeDroneToPilot extends AbstractUpgradeDrone
{
    protected ActionEnum $name = ActionEnum::UPGRADE_DRONE_TO_PILOT;

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new HasStatus([
            'status' => EquipmentStatusEnum::PILOT_DRONE_UPGRADE,
            'target' => HasStatus::PARAMETER,
            'contain' => false,
            'groups' => [ClassConstraint::VISIBILITY],
        ]));
    }

    public function upgradeStatus(): string
    {
        return EquipmentStatusEnum::PILOT_DRONE_UPGRADE;
    }

    public function upgradeLog(): string
    {
        return ActionLogEnum::UPGRADE_DRONE_TO_PILOT_SUCCESS;
    }

    public function upgradeName(): string
    {
        return 'Pilote';
    }
}
