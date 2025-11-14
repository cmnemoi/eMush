<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

final class CanOrderACrewmate extends ClassConstraint
{
    public string $message = 'You cannot order any crewmate!';
}
