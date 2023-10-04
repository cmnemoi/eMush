<?php

namespace Mush\Action\Validator;

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
