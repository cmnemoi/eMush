<?php

declare(strict_types=1);

namespace Mush\Equipment\ConfigData;

use Mush\Equipment\Entity\Dto\ReplaceEquipmentConfigDto;
use Mush\Equipment\Enum\EquipmentEnum;

abstract class ReplaceEquipmentConfigData
{
    /**
     * @return ReplaceEquipmentConfigDto[]
     */
    public static function getAll(): array
    {
        return [
            new ReplaceEquipmentConfigDto(
                name: 'replace_all_showers_by_thalasso',
                equipmentName: EquipmentEnum::THALASSO,
                replaceEquipmentName: EquipmentEnum::SHOWER,
            ),
        ];
    }
}
