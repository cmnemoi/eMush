<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

/**
 * Raises a violation if player does not have the required skill.
 */
final class HasStatusOrReachableEquipment extends ClassConstraint
{
    public string $message = 'You do not have the required skill or the reachable item to perform this action.';

    public string $status;
    public string $equipmentName;
}
