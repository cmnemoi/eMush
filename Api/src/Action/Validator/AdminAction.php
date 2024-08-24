<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

/**
 * Raises a violation if user tries to do the action is not an admin or is not on development environment.
 */
final class AdminAction extends ClassConstraint
{
    public string $message = 'You cannot do an admin action!';
}
