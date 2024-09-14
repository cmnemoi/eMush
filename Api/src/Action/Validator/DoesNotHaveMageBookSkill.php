<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

use Mush\Action\Enum\ActionImpossibleCauseEnum;

final class DoesNotHaveMageBookSkill extends ClassConstraint
{
    public string $message = ActionImpossibleCauseEnum::MAGE_BOOK_ALREADY_HAVE_SKILL;
}
