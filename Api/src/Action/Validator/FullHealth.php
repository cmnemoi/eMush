<?php

namespace Mush\Action\Validator;

class FullHealth extends ClassConstraint
{
    public const PLAYER = 'player';
    public const PARAMETER = 'parameter';

    public string $message = 'player already full health';

    public string $target;
}
