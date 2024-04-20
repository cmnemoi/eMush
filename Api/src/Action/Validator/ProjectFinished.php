<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

final class ProjectFinished extends ClassConstraint
{
    public string $message = 'This project is already finished.';
}
