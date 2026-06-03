<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

class ForbiddenLove extends ClassConstraint
{
    public string $message = 'player is related to target player';
}
