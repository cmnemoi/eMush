<?php

namespace Mush\Action\Validator;

class ParameterName extends ClassConstraint
{
    public string $message = 'Parameter name is not good';

    public array $names;
}
