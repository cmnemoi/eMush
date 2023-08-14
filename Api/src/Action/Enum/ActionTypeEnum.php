<?php

namespace Mush\Action\Enum;

use Doctrine\Common\Collections\ArrayCollection;

class ActionTypeEnum
{
    public const ACTION_TECHNICIAN = 'action_technician';
    public const ACTION_SHOOT = 'action_shoot';
    public const ACTION_AGGRESSIVE = 'action_aggressive';
    public const ACTION_HEAL = 'action_heal';
    public const ACTION_PILOT = 'action_pilot';
    public const ACTION_ATTACK = 'action_attack';
    public const ACTION_SPOKEN = 'action_spoken';
    public const ACTION_SUPER_DIRTY = 'action_super_dirty';
    public const ACTION_SHOOT_HUNTER = 'action_shoot_hunter';

    public const ACTION_ADMIN = 'action_admin';

    public static function getAll(): ArrayCollection
    {
        $actionsType = new ArrayCollection();
        $reflectionClass = new \ReflectionClass(__CLASS__);
        $constants = $reflectionClass->getConstants();
        foreach ($constants as $constant) {
            $actionsType->add($constant);
        }

        return $actionsType;
    }
}
