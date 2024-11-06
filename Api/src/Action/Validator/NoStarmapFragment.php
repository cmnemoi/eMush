<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

/**
 * Raises a violation if there are no visible starmap fragments in the room.
 */
final class NoStarmapFragment extends ClassConstraint
{
    public string $message = 'there are no starmap fragments in the room';
}
