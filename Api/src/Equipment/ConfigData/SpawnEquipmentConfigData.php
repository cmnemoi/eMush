<?php

declare(strict_types=1);

namespace Mush\Equipment\ConfigData;

use Mush\Equipment\Entity\Dto\SpawnEquipmentConfigDto;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Place\Enum\RoomEnum;

abstract class SpawnEquipmentConfigData
{
    /**
     * @return SpawnEquipmentConfigDto[]
     */
    public static function getAll(): array
    {
        return [
            new SpawnEquipmentConfigDto(
                name: '5_metal_scraps_in_engine_room',
                equipmentName: ItemEnum::METAL_SCRAPS,
                placeName: RoomEnum::ENGINE_ROOM,
                quantity: 5,
            ),
            new SpawnEquipmentConfigDto(
                name: '3_hydropots_in_hydroponic_garden',
                equipmentName: ItemEnum::HYDROPOT,
                placeName: RoomEnum::HYDROPONIC_GARDEN,
                quantity: 3,
            ),
            new SpawnEquipmentConfigDto(
                name: '1_auxiliary_terminal_in_medlab',
                equipmentName: EquipmentEnum::AUXILIARY_TERMINAL,
                placeName: RoomEnum::MEDLAB,
                quantity: 1,
            ),
            new SpawnEquipmentConfigDto(
                name: '1_auxiliary_terminal_in_engine_room',
                equipmentName: EquipmentEnum::AUXILIARY_TERMINAL,
                placeName: RoomEnum::ENGINE_ROOM,
                quantity: 1,
            ),
            new SpawnEquipmentConfigDto(
                name: '1_dynarcade_in_alpha_bay_2',
                equipmentName: EquipmentEnum::DYNARCADE,
                placeName: RoomEnum::ALPHA_BAY_2,
                quantity: 1,
            ),
        ];
    }
}
