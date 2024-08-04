<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

use Mush\Action\Enum\ActionImpossibleCauseEnum;

/**
 * Raises a violation if player wants to perform an aggressive action in a room under ceasefire.
 */
final class AggressiveActionOnCeasefire extends ClassConstraint
{
    public string $message = ActionImpossibleCauseEnum::CEASEFIRE;
}
