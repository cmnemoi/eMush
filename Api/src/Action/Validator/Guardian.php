<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

/**
 * Raises a violation if player wants to fo to a romm different from their previous one under a guardian's presence.
 */
final class Guardian extends ClassConstraint
{
    public string $message = 'You ccanot go to this room because of a guardian';
}
