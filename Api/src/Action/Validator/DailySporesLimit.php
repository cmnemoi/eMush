<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

class DailySporesLimit extends ClassConstraint
{
    public const DAEDALUS = 'daedalus';

    public string $message = 'daily spore limit reached';

    public string $target = self::DAEDALUS;
}
