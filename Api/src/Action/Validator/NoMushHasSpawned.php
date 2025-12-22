<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

/**
 * Raises a violation if all Mushs are dead.
 */
final class NoMushHasSpawned extends ClassConstraint
{
    public string $message = 'Ships has no mush';
}
