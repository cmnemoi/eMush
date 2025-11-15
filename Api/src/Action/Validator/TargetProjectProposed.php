<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

final class TargetProjectProposed extends ClassConstraint
{
    public string $message = 'The project you want to participate in is not proposed.';
}
