<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

use Mush\Project\Enum\ProjectName;

final class ProjectFinished extends ClassConstraint
{
    public string $message = 'This project is already finished.';
    public ProjectName $project;
    public string $mode = 'allow';
}
