<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

class IsActionProviderOperational extends ClassConstraint
{
    public string $message = 'action provider is not operational';
}
