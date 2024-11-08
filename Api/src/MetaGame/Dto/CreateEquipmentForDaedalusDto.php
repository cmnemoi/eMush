<?php

declare(strict_types=1);

namespace Mush\MetaGame\Dto;

final readonly class CreateEquipmentForDaedalusDto
{
    public function __construct(
        public readonly string $equipmentName,
        public readonly int $quantity,
        public readonly string $place,
        public readonly int $daedalus
    ) {}
}
