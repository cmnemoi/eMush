<?php

declare(strict_types=1);

namespace Mush\MetaGame\Dto;

final readonly class CreateEquipmentForDaedalusesDto
{
    public function __construct(
        public readonly string $equipmentName,
        public readonly int $quantity,
        public readonly string $place
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            $data['equipmentName'],
            $data['quantity'],
            $data['place']
        );
    }
}
