<?php

declare(strict_types=1);

namespace Mush\MetaGame\Dto;

final readonly class CreateProjectForDaedalusDto
{
    public function __construct(
        public readonly string $projectName,
        public readonly int $daedalus
    ) {}
}
