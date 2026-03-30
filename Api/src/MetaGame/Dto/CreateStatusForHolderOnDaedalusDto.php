<?php

declare(strict_types=1);

namespace Mush\MetaGame\Dto;

final readonly class CreateStatusForHolderOnDaedalusDto
{
    public function __construct(
        public readonly string $statusName,
        public readonly string $holder,
        public readonly int $daedalus
    ) {}
}
