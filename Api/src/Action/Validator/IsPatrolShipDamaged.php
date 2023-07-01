<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

/**
 * Will raise a violation if patrol ship is not damaged.
 */
final class IsPatrolShipDamaged extends ClassConstraint
{
    public string $message = 'patrol ship is not damaged';
}
