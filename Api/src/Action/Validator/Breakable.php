<?php

namespace Mush\Action\Validator;

final class Breakable extends ClassConstraint
{
    public string $message = 'cannot break the parameter';
}
