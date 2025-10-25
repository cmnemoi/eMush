<?php

declare(strict_types=1);

namespace Mush\Exploration\ConfigData;

use Mush\Exploration\Enum\PlanetSectorEventTagEnum;
use Mush\Exploration\Event\PlanetSectorEvent;

/** @codeCoverageIgnore */
final class PlanetSectorEventConfigData
{
    /**
     * @return PlanetSectorEventConfigDto[]
     */
    public static function getAll(): array
    {
        return [
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::ACCIDENT_3_5,
                eventName: PlanetSectorEvent::ACCIDENT,
                outputTable: [
                    3 => 1,
                    4 => 1,
                    5 => 1,
                ],
                tag: PlanetSectorEventTagEnum::NEGATIVE,
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::DISASTER_3_5,
                eventName: PlanetSectorEvent::DISASTER,
                outputTable: [
                    3 => 1,
                    4 => 1,
                    5 => 1,
                ],
                tag: PlanetSectorEventTagEnum::NEGATIVE,
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::NOTHING_TO_REPORT,
                eventName: PlanetSectorEvent::NOTHING_TO_REPORT,
                tag: PlanetSectorEventTagEnum::NEUTRAL,
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::NOTHING_TO_REPORT_NEGATIVE,
                eventName: PlanetSectorEvent::NOTHING_TO_REPORT,
                tag: PlanetSectorEventTagEnum::NEGATIVE,
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::TIRED_2,
                eventName: PlanetSectorEvent::TIRED,
                outputTable: [2 => 1],
                tag: PlanetSectorEventTagEnum::NEGATIVE,
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::OXYGEN_8,
                eventName: PlanetSectorEvent::OXYGEN,
                outputTable: [8 => 1],
                tag: PlanetSectorEventTagEnum::NEGATIVE,
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::OXYGEN_16,
                eventName: PlanetSectorEvent::OXYGEN,
                outputTable: [16 => 1],
                tag: PlanetSectorEventTagEnum::POSITIVE,
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::OXYGEN_24,
                eventName: PlanetSectorEvent::OXYGEN,
                outputTable: [24 => 1],
                tag: PlanetSectorEventTagEnum::POSITIVE,
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::FUEL_1,
                eventName: PlanetSectorEvent::FUEL,
                outputTable: [1 => 1],
                tag: PlanetSectorEventTagEnum::POSITIVE,
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::FUEL_2,
                eventName: PlanetSectorEvent::FUEL,
                outputTable: [2 => 1],
                tag: PlanetSectorEventTagEnum::POSITIVE,
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::FUEL_3,
                eventName: PlanetSectorEvent::FUEL,
                outputTable: [3 => 1],
                tag: PlanetSectorEventTagEnum::POSITIVE,
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::FUEL_3_NEGATIVE,
                eventName: PlanetSectorEvent::FUEL,
                outputTable: [3 => 1],
                tag: PlanetSectorEventTagEnum::NEGATIVE,
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::FUEL_4,
                eventName: PlanetSectorEvent::FUEL,
                outputTable: [4 => 1],
                tag: PlanetSectorEventTagEnum::POSITIVE,
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::FUEL_5,
                eventName: PlanetSectorEvent::FUEL,
                outputTable: [5 => 1],
                tag: PlanetSectorEventTagEnum::POSITIVE,
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::FUEL_6,
                eventName: PlanetSectorEvent::FUEL,
                outputTable: [6 => 1],
                tag: PlanetSectorEventTagEnum::POSITIVE,
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::ARTEFACT,
                eventName: PlanetSectorEvent::ARTEFACT,
                outputQuantity: [1 => 1],
                outputTable: [
                    'alien_bottle_opener' => 1,
                    'alien_holographic_tv' => 1,
                    'invertebrate_shell' => 1,
                    'jar_of_alien_oil' => 1,
                    'magellan_liquid_map' => 1,
                    'printed_circuit_jelly' => 1,
                    'rolling_boulder' => 1,
                    'starmap_fragment' => 1,
                    'water_stick' => 1,
                ],
                tag: PlanetSectorEventTagEnum::POSITIVE,
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::KILL_RANDOM,
                eventName: PlanetSectorEvent::KILL_RANDOM,
                tag: PlanetSectorEventTagEnum::NEGATIVE,
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::KILL_ALL,
                eventName: PlanetSectorEvent::KILL_ALL,
                tag: PlanetSectorEventTagEnum::NEGATIVE,
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::PROVISION_1,
                eventName: PlanetSectorEvent::PROVISION,
                outputQuantity: [1 => 1],
                outputTable: [
                    'alien_steak' => 1,
                ],
                tag: PlanetSectorEventTagEnum::POSITIVE,
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::PROVISION_2,
                eventName: PlanetSectorEvent::PROVISION,
                outputQuantity: [2 => 1],
                outputTable: [
                    'alien_steak' => 1,
                ],
                tag: PlanetSectorEventTagEnum::POSITIVE,
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::PROVISION_3,
                eventName: PlanetSectorEvent::PROVISION,
                outputQuantity: [3 => 1],
                outputTable: [
                    'alien_steak' => 1,
                ],
                tag: PlanetSectorEventTagEnum::POSITIVE,
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::PROVISION_4,
                eventName: PlanetSectorEvent::PROVISION,
                outputQuantity: [4 => 1],
                outputTable: [
                    'alien_steak' => 1,
                ],
                tag: PlanetSectorEventTagEnum::POSITIVE,
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::FIGHT_8,
                eventName: PlanetSectorEvent::FIGHT,
                outputQuantity: [5 => 1], // disease chance
                outputTable: [8 => 1], // creature strength
                tag: PlanetSectorEventTagEnum::NEGATIVE,
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::FIGHT_10,
                eventName: PlanetSectorEvent::FIGHT,
                outputQuantity: [5 => 1],
                outputTable: [10 => 1],
                tag: PlanetSectorEventTagEnum::NEGATIVE,
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::FIGHT_12,
                eventName: PlanetSectorEvent::FIGHT,
                outputQuantity: [5 => 1],
                outputTable: [12 => 1],
                tag: PlanetSectorEventTagEnum::NEGATIVE,
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::FIGHT_15,
                eventName: PlanetSectorEvent::FIGHT,
                outputQuantity: [5 => 1],
                outputTable: [15 => 1],
                tag: PlanetSectorEventTagEnum::NEGATIVE,
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::FIGHT_18,
                eventName: PlanetSectorEvent::FIGHT,
                outputQuantity: [5 => 1],
                outputTable: [18 => 1],
                tag: PlanetSectorEventTagEnum::NEGATIVE,
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::FIGHT_32,
                eventName: PlanetSectorEvent::FIGHT,
                outputQuantity: [5 => 1],
                outputTable: [32 => 1],
                tag: PlanetSectorEventTagEnum::NEGATIVE,
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::FIGHT_8_10_12_15_18_32,
                eventName: PlanetSectorEvent::FIGHT,
                outputQuantity: [5 => 1],
                outputTable: [
                    8 => 1,
                    10 => 1,
                    12 => 1,
                    15 => 1,
                    18 => 1,
                    32 => 1,
                ],
                tag: PlanetSectorEventTagEnum::NEGATIVE,
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::HARVEST_1,
                eventName: PlanetSectorEvent::HARVEST,
                outputQuantity: [1 => 1],
                outputTable: [
                    'creepnut' => 1,
                    'meztine' => 1,
                    'guntiflop' => 1,
                    'ploshmina' => 1,
                    'precati' => 1,
                    'bottine' => 1,
                    'fragilane' => 1,
                    'anemole' => 1,
                    'peniraft' => 1,
                    'kubinus' => 1,
                    'caleboot' => 1,
                    'filandra' => 1,
                ],
                tag: PlanetSectorEventTagEnum::POSITIVE,
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::HARVEST_2,
                eventName: PlanetSectorEvent::HARVEST,
                outputQuantity: [2 => 1],
                outputTable: [
                    'creepnut' => 1,
                    'meztine' => 1,
                    'guntiflop' => 1,
                    'ploshmina' => 1,
                    'precati' => 1,
                    'bottine' => 1,
                    'fragilane' => 1,
                    'anemole' => 1,
                    'peniraft' => 1,
                    'kubinus' => 1,
                    'caleboot' => 1,
                    'filandra' => 1,
                ],
                tag: PlanetSectorEventTagEnum::POSITIVE,
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::HARVEST_3,
                eventName: PlanetSectorEvent::HARVEST,
                outputQuantity: [3 => 1],
                outputTable: [
                    'creepnut' => 1,
                    'meztine' => 1,
                    'guntiflop' => 1,
                    'ploshmina' => 1,
                    'precati' => 1,
                    'bottine' => 1,
                    'fragilane' => 1,
                    'anemole' => 1,
                    'peniraft' => 1,
                    'kubinus' => 1,
                    'caleboot' => 1,
                    'filandra' => 1,
                ],
                tag: PlanetSectorEventTagEnum::POSITIVE,
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::DISEASE,
                eventName: PlanetSectorEvent::DISEASE,
                tag: PlanetSectorEventTagEnum::NEGATIVE,
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::STARMAP,
                eventName: PlanetSectorEvent::STARMAP,
                outputQuantity: [1 => 1],
                outputTable: [
                    'starmap_fragment' => 1,
                ],
                tag: PlanetSectorEventTagEnum::POSITIVE,
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::MUSH_TRAP,
                eventName: PlanetSectorEvent::MUSH_TRAP,
                outputQuantity: [50 => 1], // odds (percentage)
                tag: PlanetSectorEventTagEnum::NEGATIVE,
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::AGAIN,
                eventName: PlanetSectorEvent::AGAIN,
                tag: PlanetSectorEventTagEnum::NEUTRAL,
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::ITEM_LOST,
                eventName: PlanetSectorEvent::ITEM_LOST,
                tag: PlanetSectorEventTagEnum::NEGATIVE,
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::BACK,
                eventName: PlanetSectorEvent::BACK,
                tag: PlanetSectorEventTagEnum::NEUTRAL,
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::PLAYER_LOST,
                eventName: PlanetSectorEvent::PLAYER_LOST,
                tag: PlanetSectorEventTagEnum::NEGATIVE,
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::FIND_LOST,
                eventName: PlanetSectorEvent::FIND_LOST,
                tag: PlanetSectorEventTagEnum::POSITIVE,
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::KILL_LOST,
                eventName: PlanetSectorEvent::KILL_LOST,
                tag: PlanetSectorEventTagEnum::NEGATIVE,
            ),
        ];
    }
}
