<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

class ModifierPreventAction extends ClassConstraint
{
    public string $message = 'player has modifier that prevent this action';
}
