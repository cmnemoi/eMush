<?php

declare(strict_types=1);

namespace Mush\Equipment\Entity\Dto;

use Mush\Equipment\Entity\Config\SpawnEquipmentConfig;

final readonly class SpawnEquipmentConfigDto
{
    public function __construct(
        public string $name,
        public string $equipmentName,
        public string $placeName,
        public int $quantity,
    ) {}

    public function toEntity(): SpawnEquipmentConfig
    {
        return new SpawnEquipmentConfig(
            $this->name,
            $this->equipmentName,
            $this->placeName,
            $this->quantity
        );
    }
}
