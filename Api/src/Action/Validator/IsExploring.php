<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

/**
 * Raises a violation if player trying to do the action is not exploring.
 */
final class IsExploring extends ClassConstraint
{
    public string $message = 'You are not exploring';
}
