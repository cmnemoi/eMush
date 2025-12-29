<?php

namespace Mush\Action\Validator;

/**
 * Raises a violation if the game is (not) past the Starting phase.
 *
 * @param bool $isStarting if true, game must not have started. If false, game must have started. (default: false)
 */
class PreMush extends ClassConstraint
{
    public string $message = 'action cannot be done during this game phase';
    public bool $isStarting = false;
}
