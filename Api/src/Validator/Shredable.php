<?php


namespace Mush\Action\Validator;


use Mush\Action\Validator\ClassConstraint;

class Shredable extends ClassConstraint
{
    public string $message = 'cannot shred the parameter';
}