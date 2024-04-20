<?php

namespace Mush\Action\Enum;

use Doctrine\Common\Collections\ArrayCollection;

abstract class ActionTypeEnum
{
    public const string ACTION_TECHNICIAN = 'action_technician';
    public const string ACTION_SHOOT = 'action_shoot';
    public const string ACTION_AGGRESSIVE = 'action_aggressive';
    public const string ACTION_HEAL = 'action_heal';
    public const string ACTION_PILOT = 'action_pilot';
    public const string ACTION_ATTACK = 'action_attack';
    public const string ACTION_SPOKEN = 'action_spoken';
    public const string ACTION_SUPER_DIRTY = 'action_super_dirty';
    public const string ACTION_SHOOT_HUNTER = 'action_shoot_hunter';
    public const string ACTION_CONFIRM = 'action_confirm';
    public const string ACTION_ADMIN = 'action_admin';

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
