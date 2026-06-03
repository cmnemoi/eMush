<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

class Cookable extends ClassConstraint
{
    public string $message = 'cannot cook the parameter';
}
