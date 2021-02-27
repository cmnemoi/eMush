<?php

namespace Mush\Action\Validator;

use Symfony\Component\Validator\Constraint;

abstract class ClassConstraint extends Constraint
{
    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}