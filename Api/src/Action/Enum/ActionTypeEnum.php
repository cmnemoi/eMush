<?php

namespace Mush\Action\Enum;

class ActionTypeEnum
{
    public const ACTION_TECHNICIAN = 'action_technician';
    public const ACTION_SHOOT = 'action_shoot';
    public const ACTION_AGGRESSIVE = 'action_aggressive';
    public const ACTION_HEAL = 'action_heal';
    public const ACTION_PILOT = 'action_pilot';
    public const ACTION_ATTACK = 'action_attack';
    public const ACTION_SPOKEN = 'action_spoken';

    public static function getTechnicianActions() : array
    {
        return [
            ActionEnum::STRENGTHEN_HULL,
            ActionEnum::REPAIR,
            ActionEnum::DISASSEMBLE,
        ];
    }

    public static function getAgressiveActions(): array
    {
        return [
            ActionEnum::HIT,
            ActionEnum::SHOOT
        ];
    }
}
