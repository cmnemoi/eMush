<?php


namespace Mush\Action\Validator;


use Mush\Action\Validator\ClassConstraint;

class Hideable extends ClassConstraint
{
    public string $message = 'cannot hide parameter';
}