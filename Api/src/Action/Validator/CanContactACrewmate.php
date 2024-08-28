<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

final class CanContactACrewmate extends ClassConstraint
{
    public string $message = 'You cannot contact any crewmate!';
}
