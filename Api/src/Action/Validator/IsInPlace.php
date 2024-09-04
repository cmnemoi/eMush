<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

/**
 * Raises a violation if the player is not in the given place.
 *
 * @param string $place The place name to check
 */
final class IsInPlace extends ClassConstraint
{
    public string $message = 'player is not in place';

    public string $place;
}
