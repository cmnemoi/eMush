<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

/**
 * Raises a violation is equipment has a one of the infected status.
 */
class EquipmentInfected extends ClassConstraint
{
    public string $message = 'equipment has one of the infected status';
}
