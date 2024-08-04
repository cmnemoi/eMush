<?php

namespace Mush\Action\Validator;

use Symfony\Component\Validator\Constraint;

abstract class ClassConstraint extends Constraint
{
    public const string EXECUTE = 'execute';
    public const string VISIBILITY = 'visibility';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
