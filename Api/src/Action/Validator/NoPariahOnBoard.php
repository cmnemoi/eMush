<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

use Mush\Action\Enum\ActionImpossibleCauseEnum;

/**
 * Raises a violation if there is already a pariah on board.
 */
final class NoPariahOnBoard extends ClassConstraint
{
    public string $message = ActionImpossibleCauseEnum::ALREADY_OUTCAST_ONBOARD;
}
