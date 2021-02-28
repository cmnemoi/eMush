<?php

namespace Mush\Action\Validator;

class Breakable extends ClassConstraint
{
    public string $message = 'cannot break the parameter';
}
