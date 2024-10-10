<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

use Mush\Project\Enum\ProjectType as ProjectTypeEnum;

/**
 * Prevents action if project types does not match any of the required ones.
 */
final class ProjectType extends ClassConstraint
{
    public string $message = 'project type does not match any of the required types';

    /** @var ProjectTypeEnum[] */
    public array $types;
}
