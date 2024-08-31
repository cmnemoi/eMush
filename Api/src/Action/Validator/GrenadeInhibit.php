<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

/**
 * Raises a violation if NERON is inhibited and player trying to do the action is human.
 */
final class GrenadeInhibit extends ClassConstraint
{
    public string $message = 'You cannot launch a grenade with DMZ-Corepeace inhibit activated.';
}
