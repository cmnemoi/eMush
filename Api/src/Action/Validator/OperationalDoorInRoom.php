<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

/**
 * Raises a violation if there is no operational door in player's room.
 */
final class OperationalDoorInRoom extends ClassConstraint
{
    public string $message = 'there is no operational door in the room';
}
