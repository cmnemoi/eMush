<?php

namespace Mush\Daedalus\ConfigData;

use Mush\Daedalus\Enum\CharacterSetEnum;
use Mush\Game\Enum\HolidayEnum;
use Mush\Place\Enum\RoomEnum;

/** @codeCoverageIgnore */
class DaedalusConfigData
{
    public static array $dataArray = [
        [
            'name' => 'default',
            'initOxygen' => 32,
            'initFuel' => 20,
            'initHull' => 100,
            'initShield' => 50,
            'initHunterPoints' => 40,
            'initCombustionChamberFuel' => 0,
            'maxOxygen' => 32,
            'maxFuel' => 32,
            'maxHull' => 100,
            'maxShield' => 100,
            'maxCombustionChamberFuel' => 9,
            'dailySporeNb' => 4,
            'nbMush' => 2,
            'cyclePerGameDay' => 8,
            'cycleLength' => 3,
            'randomItemPlaces' => 'default',
            'startingApprentrons' => [
                'apprentron_technician' => 14,
                'apprentron_pilot' => 12,
                'apprentron_astrophysicist' => 10,
                'apprentron_biologist' => 10,
                'apprentron_botanist' => 10,
                'apprentron_shooter' => 10,
                'apprentron_radio_expert' => 9,
                'apprentron_medic' => 8,
                'apprentron_sprinter' => 8,
                'apprentron_shrink' => 6,
                'apprentron_robotics_expert' => 4,
                'apprentron_firefighter' => 2,
                'apprentron_it_expert' => 2,
                'apprentron_diplomat' => 2,
                'apprentron_logistics_expert' => 2,
            ],
            'placeConfigs' => [
                RoomEnum::BRIDGE . '_default',
                RoomEnum::ALPHA_BAY . '_default',
                RoomEnum::BRAVO_BAY . '_default',
                RoomEnum::ALPHA_BAY_2 . '_default',
                RoomEnum::NEXUS . '_default',
                RoomEnum::MEDLAB . '_default',
                RoomEnum::LABORATORY . '_default',
                RoomEnum::REFECTORY . '_default',
                RoomEnum::HYDROPONIC_GARDEN . '_default',
                RoomEnum::ENGINE_ROOM . '_default',
                RoomEnum::FRONT_ALPHA_TURRET . '_default',
                RoomEnum::CENTRE_ALPHA_TURRET . '_default',
                RoomEnum::REAR_ALPHA_TURRET . '_default',
                RoomEnum::FRONT_BRAVO_TURRET . '_default',
                RoomEnum::CENTRE_BRAVO_TURRET . '_default',
                RoomEnum::REAR_BRAVO_TURRET . '_default',
                RoomEnum::FRONT_CORRIDOR . '_default',
                RoomEnum::CENTRAL_CORRIDOR . '_default',
                RoomEnum::REAR_CORRIDOR . '_default',
                RoomEnum::ICARUS_BAY . '_default',
                RoomEnum::ALPHA_DORM . '_default',
                RoomEnum::BRAVO_DORM . '_default',
                RoomEnum::FRONT_STORAGE . '_default',
                RoomEnum::CENTER_ALPHA_STORAGE . '_default',
                RoomEnum::REAR_ALPHA_STORAGE . '_default',
                RoomEnum::CENTER_BRAVO_STORAGE . '_default',
                RoomEnum::REAR_BRAVO_STORAGE . '_default',
                RoomEnum::SPACE . '_default',
                RoomEnum::PATROL_SHIP_ALPHA_2_WALLIS . '_default',
                RoomEnum::PATROL_SHIP_ALPHA_LONGANE . '_default',
                RoomEnum::PATROL_SHIP_ALPHA_JUJUBE . '_default',
                RoomEnum::PATROL_SHIP_ALPHA_TAMARIN . '_default',
                RoomEnum::PATROL_SHIP_BRAVO_EPICURE . '_default',
                RoomEnum::PATROL_SHIP_BRAVO_PLANTON . '_default',
                RoomEnum::PATROL_SHIP_BRAVO_SOCRATE . '_default',
                RoomEnum::PASIPHAE . '_default',
                RoomEnum::PLANET . '_default',
                RoomEnum::PLANET_DEPTHS . '_default',
                RoomEnum::TABULATRIX_QUEUE . '_default',
            ],
            'numberOfProjectsByBatch' => 3,
            'humanSkillSlots' => 3,
            'mushSkillSlots' => 4,
            'applyHoliday' => HolidayEnum::CURRENT,
            'freeLove' => true,
            'numberOfCyclesBeforeNextRebelBaseContact' => 8,
            'rebelBaseContactDurationMin' => 8,
            'rebelBaseContactDurationMax' => 16,
            'startingRandomBlueprintCount' => 4,
            'randomBlueprints' => [
                'oscilloscope_blueprint' => 4,
                'sniper_helmet_blueprint' => 2,
                'rocket_launcher_blueprint' => 2,
                'lizaro_jungle_blueprint' => 4,
                'old_faithful_blueprint' => 2,
                'white_flag_blueprint' => 4,
                'babel_module_blueprint' => 4,
                'echolocator_blueprint' => 8,
                'thermosensor_blueprint' => 2,
                'extinguisher_blueprint' => 4,
                'swedish_sofa_blueprint' => 4,
                'grenade_blueprint' => 2,
                'support_drone_blueprint' => 1,
            ],
            'playerCount' => 16,
            'chaolaToggle' => CharacterSetEnum::FINOLA_CHAO,
        ],
    ];

    public static function getByName(string $name): array
    {
        return current(
            array_filter(
                self::$dataArray,
                static fn (array $data) => $data['name'] === $name
            )
        );
    }
}
