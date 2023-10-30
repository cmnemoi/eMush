<?php

namespace Mush\Action\Validator;

/**
 * Prevents the action if the pasiphae is still available, and player not in the Pasiphae.
 */
class IsPasiphaeDestroyed extends ClassConstraint
{
    public string $message = 'The Pasiphae is still functional.';
}
