<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

/**
 * Raises a violation if the action provider is not in the player inventory.
 */
final class ActionProviderIsInPlayerInventory extends ClassConstraint
{
    public string $message = 'The action provider must be in the player inventory.';
}
