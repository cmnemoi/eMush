<?php


namespace Mush\Action\Validator;


use Symfony\Component\Validator\Constraint;

class Reach extends ClassConstraint
{
    public string $message = 'player cannot reach parameter';

}