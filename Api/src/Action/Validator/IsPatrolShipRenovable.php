<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

/**
 * Will raise a violation if patrol ship is not renovable :
 * - patrol ship is not damaged (its armor is maximum)
 * - patrol ship is not broken.
 */
final class IsPatrolShipRenovable extends ClassConstraint
{
    public string $message = 'patrol ship is not renovable';
}
