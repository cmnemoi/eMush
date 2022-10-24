<?php

namespace Mush\Action\Validator;

class HasEquipment extends ClassConstraint
{
    public const PLAYER = 'player';
    public const PARAMETER = 'parameter';

    public string $message = 'an equipment is missing or should not be present';

    public string $reach;
    public array $equipments;

    // either all the equipments or any
    public bool $all = true;

    public bool $contains = true;

    // if true, checks that the equipment is currently working
    public bool $checkIfOperational = false;

    // This validator can also apply to a targeted player
    public string $target = self::PLAYER;
}
