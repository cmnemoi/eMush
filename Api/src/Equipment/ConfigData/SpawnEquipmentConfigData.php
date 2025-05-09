<?php

declare(strict_types=1);

namespace Mush\Equipment\ConfigData;

use Mush\Equipment\Entity\Dto\SpawnEquipmentConfigDto;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Place\Enum\RoomEnum;

abstract class SpawnEquipmentConfigData
{
    public const string TWO_CAMERAS_IN_PLAYER_INVENTORY = 'two_cameras_in_player_inventory';
    public const string TWO_HYDROPOTS_IN_PLAYER_INVENTORY = 'two_hydropots_in_player_inventory';
    public const string ONE_SUPPORT_DRONE_BLUEPRINT_IN_PLAYER_INVENTORY = 'one_support_drone_blueprint_in_player_inventory';
    public const string FOUR_ANABOLICS_IN_LABORATORY = 'four_anabolics_in_laboratory';
    public const string ONE_RETRO_FUNGAL_SERUM_IN_LABORATORY = 'one_retrofungal_serum_in_laboratory';
    public const string ONE_CALCULATOR_IN_NEXUS = 'one_calculator_in_nexus';
    public const string ONE_MYCOSCAN_IN_LABORATORY = 'one_mycoscan_in_laboratory';
    public const string ONE_NARCOTICS_DISTILLER_IN_MEDLAB = 'one_narcotics_distiller_in_medlab';
    public const string TWO_NCC_LENSES_IN_LABORATORY = 'two_ncc_lenses_in_laboratory';
    public const string ONE_SPORE_SUCKER_IN_LABORATORY = 'one_spore_sucker_in_laboratory';
    public const string FIVE_MYCOALARMS_IN_LABORATORY = 'five_mycoalarms_in_laboratory';

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
            new SpawnEquipmentConfigDto(
                name: '1_support_drone_in_nexus',
                equipmentName: ItemEnum::SUPPORT_DRONE,
                placeName: RoomEnum::NEXUS,
                quantity: 1,
            ),
            new SpawnEquipmentConfigDto(
                name: '4_metal_scraps_in_engine_room',
                equipmentName: ItemEnum::METAL_SCRAPS,
                placeName: RoomEnum::ENGINE_ROOM,
                quantity: 4,
            ),
            new SpawnEquipmentConfigDto(
                name: '4_plastic_scraps_in_engine_room',
                equipmentName: ItemEnum::PLASTIC_SCRAPS,
                placeName: RoomEnum::ENGINE_ROOM,
                quantity: 4,
            ),
            new SpawnEquipmentConfigDto(
                name: '1_hydroponic_incubator_in_hydroponic_garden',
                equipmentName: EquipmentEnum::HYDROPONIC_INCUBATOR,
                placeName: RoomEnum::HYDROPONIC_GARDEN,
                quantity: 1,
            ),
            new SpawnEquipmentConfigDto(
                name: '1_jukebox_blueprint_in_nexus',
                equipmentName: 'jukebox_blueprint',
                placeName: RoomEnum::NEXUS,
                quantity: 1,
            ),
            new SpawnEquipmentConfigDto(
                name: self::TWO_HYDROPOTS_IN_PLAYER_INVENTORY,
                equipmentName: ItemEnum::HYDROPOT,
                quantity: 2,
            ),
            new SpawnEquipmentConfigDto(
                name: self::TWO_CAMERAS_IN_PLAYER_INVENTORY,
                equipmentName: ItemEnum::CAMERA_ITEM,
                quantity: 2,
            ),
            new SpawnEquipmentConfigDto(
                name: self::ONE_SUPPORT_DRONE_BLUEPRINT_IN_PLAYER_INVENTORY,
                equipmentName: 'support_drone_blueprint',
                quantity: 1,
            ),
            new SpawnEquipmentConfigDto(
                name: 'schrodinger_in_laboratory',
                equipmentName: ItemEnum::SCHRODINGER,
                placeName: RoomEnum::LABORATORY,
                quantity: 1,
            ),
            new SpawnEquipmentConfigDto(
                name: self::FOUR_ANABOLICS_IN_LABORATORY,
                equipmentName: GameRationEnum::ANABOLIC,
                placeName: RoomEnum::LABORATORY,
                quantity: 4,
            ),
            new SpawnEquipmentConfigDto(
                name: self::ONE_CALCULATOR_IN_NEXUS,
                equipmentName: EquipmentEnum::CALCULATOR,
                placeName: RoomEnum::NEXUS,
                quantity: 1,
            ),
            new SpawnEquipmentConfigDto(
                name: self::ONE_RETRO_FUNGAL_SERUM_IN_LABORATORY,
                equipmentName: ToolItemEnum::RETRO_FUNGAL_SERUM,
                placeName: RoomEnum::LABORATORY,
                quantity: 1,
            ),
            new SpawnEquipmentConfigDto(
                name: self::ONE_MYCOSCAN_IN_LABORATORY,
                equipmentName: EquipmentEnum::MYCOSCAN,
                placeName: RoomEnum::LABORATORY,
                quantity: 1,
            ),
            new SpawnEquipmentConfigDto(
                name: self::ONE_NARCOTICS_DISTILLER_IN_MEDLAB,
                equipmentName: EquipmentEnum::NARCOTIC_DISTILLER,
                placeName: RoomEnum::MEDLAB,
                quantity: 1,
            ),
            new SpawnEquipmentConfigDto(
                name: self::TWO_NCC_LENSES_IN_LABORATORY,
                equipmentName: GearItemEnum::NCC_LENS,
                placeName: RoomEnum::LABORATORY,
                quantity: 2,
            ),
            new SpawnEquipmentConfigDto(
                name: self::ONE_SPORE_SUCKER_IN_LABORATORY,
                equipmentName: ToolItemEnum::SPORE_SUCKER,
                placeName: RoomEnum::LABORATORY,
                quantity: 1,
            ),
            new SpawnEquipmentConfigDto(
                name: self::FIVE_MYCOALARMS_IN_LABORATORY,
                equipmentName: ItemEnum::MYCO_ALARM,
                placeName: RoomEnum::LABORATORY,
                quantity: 5,
            ),
        ];
    }
}
