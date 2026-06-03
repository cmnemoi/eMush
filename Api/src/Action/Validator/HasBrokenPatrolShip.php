<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

class HasBrokenPatrolShip extends ClassConstraint
{
    public string $message = 'Cannot take off with a broken patrol ship';
}
