<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

/**
 * Raises a violation if :
 * - there are at least 4 players alive in Icarus Bay
 * - the player tries to go to Icarus Bay
 */
final class CanGoToIcarusBay extends ClassConstraint
{
    public string $message = 'you cannot go to Icarus Bay';
}
