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
            new ReplaceEquipmentConfigDto(
                name: 'replace_planet_scanner_by_quantum_sensors_planet_scanner',
                equipmentName: EquipmentEnum::QUANTUM_SENSORS_PLANET_SCANNER,
                replaceEquipmentName: EquipmentEnum::PLANET_SCANNER,
            ),
        ];
    }
}
