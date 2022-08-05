<?php

namespace Mush\Game\Validator;

use Symfony\Component\Validator\Constraint;

interface InputConstraintInterface
{
    public function getConstraints(): Constraint;
}
