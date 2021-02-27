<?php


namespace Mush\Action\Validator;


use Mush\Action\Validator\ClassConstraint;
use Mush\Equipment\Enum\ReachEnum;

class Breakable extends ClassConstraint
{
    public string $message = 'cannot break the parameter';
}