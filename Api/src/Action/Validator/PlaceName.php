<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

/**
 * Raises a violation if the player is not in the expected places.
 *
 * @param array<string> $places list of places
 */
final class PlaceName extends ClassConstraint
{
    public string $message = 'place is not the expected type';
    public array $places;
}
