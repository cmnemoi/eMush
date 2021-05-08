<?php

namespace Mush\Action\Validator;

class DailySporesLimit extends ClassConstraint
{
    public const DAEDALUS = 'daedalus';
    public const PLAYER = 'player';

    public string $message = 'daily spore limit reached';

    public string $target = self::DAEDALUS;
}
