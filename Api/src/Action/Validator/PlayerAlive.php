<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

class PlayerAlive extends ClassConstraint
{
    public string $message = 'player is dead';
}
