<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

final class TargetProjectFinished extends ClassConstraint
{
    public string $message = 'The project you want to participate in is already finished.';
}
