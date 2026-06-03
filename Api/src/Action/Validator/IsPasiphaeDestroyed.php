<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

/**
 * Prevents the action if the pasiphae is still available, and player not in the Pasiphae.
 */
final class IsPasiphaeDestroyed extends ClassConstraint
{
    public string $message = 'The Pasiphae is still functional.';
}
