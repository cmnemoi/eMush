<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

/**
 * Raises a violation if all Mushs are dead.
 */
final class AllMushsAreDead extends ClassConstraint
{
    public string $message = 'All mushs are dead';
}
