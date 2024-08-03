<?php

namespace Mush\Action\Enum;

use Doctrine\Common\Collections\ArrayCollection;

enum ActionTypeEnum: string
{
    case ACTION_TECHNICIAN = 'action_technician';
    case ACTION_CONCEPTOR = 'action_conceptor';
    case ACTION_SHOOT = 'action_shoot';
    case ACTION_AGGRESSIVE = 'action_aggressive';
    case ACTION_HEAL = 'action_heal';
    case ACTION_PILOT = 'action_pilot';
    case ACTION_ATTACK = 'action_attack';
    case ACTION_SPOKEN = 'action_spoken';
    case ACTION_SUPER_DIRTY = 'action_super_dirty';
    case ACTION_SHOOT_HUNTER = 'action_shoot_hunter';
    case ACTION_CONFIRM = 'action_confirm';
    case ACTION_ADMIN = 'action_admin';
    case ACTION_BOTANIST = 'action_botanist';
    case ACTION_IT = 'action_it';
    case ACTION_COOK = 'action_cook';
    case ACTION_PILGRED = 'action_pilgred';
    case ACTION_ZERO_ACTION_COST = 'action_zero_action_cost';

    public function toString(): string
    {
        return $this->value;
    }

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
