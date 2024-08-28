<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

/**
 * Raises a violation if player has no pending missions.
 */
final class PlayerHasPendingMissions extends ClassConstraint
{
    public string $message = 'You do not have any pending missions!';
}
