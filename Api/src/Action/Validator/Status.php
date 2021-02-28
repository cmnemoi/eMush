<?php

namespace Mush\Action\Validator;

class Status extends ClassConstraint
{
    public const PLAYER = 'player';
    public const PARAMETER = 'parameter';
    public const PLAYER_ROOM = 'player_room';

    public string $message = 'parameter do not match expected status';

    public string $status;

    public bool $contain = true;

    //If not target player, then it targets the parameter
    public string $target = self::PARAMETER;
}
