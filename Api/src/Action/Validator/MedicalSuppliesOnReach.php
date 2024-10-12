<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

/**
 * Raises a violation if the player is not in Medlab or does not have the medikit on his inventory.
 */
final class MedicalSuppliesOnReach extends ClassConstraint
{
    public string $message = 'You should be in Medlab or have the medikit on your inventory to perform this action';
}
