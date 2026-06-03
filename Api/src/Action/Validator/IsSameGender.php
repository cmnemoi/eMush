<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

class IsSameGender extends ClassConstraint
{
    public string $message = 'target player is the same gender as player and freeLove is not on';
}
