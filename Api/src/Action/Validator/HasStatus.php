<?php

namespace Mush\Action\Validator;

/**
 * Raises a violation given the status of the target.
 *
 * @param string $status              The status name to check
 * @param string $target              The target of the status (default: "parameter"). Available values are: HasStatus::PLAYER, HasStatus::PARAMETER, HasStatus::PLAYER_ROOM, HasStatus::DAEDALUS
 * @param bool   $contain             If true, the status must be present for the action to be allowed, if false, the status must not be present (default: true)
 * @param bool   $ownerSide           If true, the status is checked on the owner side, if false, the status is checked on the target side (default: true)
 * @param bool   $bypassIfUserIsAdmin If true, the status check is bypassed if the user is admin (default: false)
 */
class HasStatus extends ClassConstraint
{
    public const PLAYER = 'player';
    public const PARAMETER = 'parameter';
    public const PLAYER_ROOM = 'player_room';
    public const DAEDALUS = 'daedalus';

    public string $message = 'parameter do not match expected status';

    public string $status;

    public bool $contain = true;

    // If not target player, then it targets the parameter
    public string $target = self::PARAMETER;

    // check on the owner side, if false check target side of the status
    public bool $ownerSide = true;

    public bool $bypassIfUserIsAdmin = false;
}
