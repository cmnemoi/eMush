<?php

declare(strict_types=1);

namespace Mush\Project\Dto;

use Mush\Project\Enum\ProjectRequirementName;
use Mush\Project\Enum\ProjectRequirementType;

final readonly class ProjectRequirementConfigDto
{
    public function __construct(
        public ProjectRequirementName $name,
        public ProjectRequirementType $type,
        public string $target = ''
    ) {}
}
