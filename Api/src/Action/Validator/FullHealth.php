<?php

namespace Mush\Action\Validator;

class FullHealth extends ClassConstraint
{
    public const PLAYER = 'player';
    public const PARAMETER = 'parameter';

    public string $message = 'player already full health';

    //If not target player, then it targets the parameter
    public string $target = self::PARAMETER;
}
