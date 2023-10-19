<?php

namespace Mush\Action\Validator;

/**
 * Validates that a player has (not) a specific equipment on reach.
 *
 * @param string $reach              reach required (inventory, shelve, room, shelve_not_hidden, daedalus)
 * @param array  $equipments         list of equipment required
 * @param bool   $all                if true, all the equipment are required, if false, any of them (default: true)
 * @param bool   $contains           if true, the equipment must be present, if false, they must not be present (default: true)
 * @param bool   $checkIfOperational if true, checks that the equipment is currently working (default: false)
 * @param string $target             either player or parameter (default: player)
 */
class HasEquipment extends ClassConstraint
{
    public const PLAYER = 'player';
    public const PARAMETER = 'parameter';

    public string $message = 'an equipment is missing or should not be present';

    public string $reach;
    public array $equipments;
    public bool $all = true;
    public bool $contains = true;
    public bool $checkIfOperational = false;
    public string $target = self::PLAYER;
}
