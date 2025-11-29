<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

/**
 * Raises a violation if the exploration is already sabotaged.
 */
final class ExplorationAlreadySabotaged extends ClassConstraint
{
    public string $message = 'Next sector is already sabotaged';
}
