<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

/**
 * Raises a violation if no alpha mush were created at the end of the Starting phase.
 */
final class NoMushHasSpawned extends ClassConstraint
{
    public string $message = 'Ships has no mush';
}
