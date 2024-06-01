<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

use Mush\Project\Enum\ProjectType as ProjectTypeEnum;

/**
 * Prevents action if project type does not match the required one.
 */
final class ProjectType extends ClassConstraint
{
    public string $message = 'project type does not match the required type';

    public ProjectTypeEnum $type;
}
