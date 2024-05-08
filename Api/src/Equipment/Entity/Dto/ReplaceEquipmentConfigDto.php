<?php

declare(strict_types=1);

namespace Mush\Equipment\Entity\Dto;

use Mush\Equipment\Entity\Config\ReplaceEquipmentConfig;

final readonly class ReplaceEquipmentConfigDto
{
    public function __construct(
        public string $name,
        public string $equipmentName,
        public string $replaceEquipmentName,
    ) {
    }

    public function toEntity(): ReplaceEquipmentConfig
    {
        return new ReplaceEquipmentConfig(
            name: $this->name,
            equipmentName: $this->equipmentName,
            replacedEquipmentName: $this->replaceEquipmentName,
        );
    }
}
