<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

class AreLateralReactorsBroken extends ClassConstraint
{
    public string $message = 'Both lateral reactors are broken';
}
