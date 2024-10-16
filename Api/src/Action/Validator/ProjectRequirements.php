<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

final class ProjectRequirements extends ClassConstraint
{
    public string $message = 'The project you want to participate in does not meet the necessary requirements.';
}
