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
            new ReplaceEquipmentConfigDto(
                name: 'replace_antenna_by_radar_trans_void_antenna',
                equipmentName: EquipmentEnum::RADAR_TRANS_VOID_ANTENNA,
                replaceEquipmentName: EquipmentEnum::ANTENNA,
            ),
        ];
    }
}
