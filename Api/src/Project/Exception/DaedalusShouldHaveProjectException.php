<?php

declare(strict_types=1);

namespace Mush\Project\Exception;

use Mush\Project\Enum\ProjectName;

final class DaedalusShouldHaveProjectException extends \RuntimeException
{
    public function __construct(ProjectName $name)
    {
        parent::__construct(\sprintf('Daedalus should have a "%s" project.', $name->value));
    }
}
