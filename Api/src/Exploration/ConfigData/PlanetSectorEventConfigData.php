<?php

declare(strict_types=1);

namespace Mush\Exploration\ConfigData;

use Mush\Equipment\Enum\GameFruitEnum;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Enum\ItemEnum;
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
                tags: [PlanetSectorEventTagEnum::NEGATIVE],
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::DISASTER_3_5,
                eventName: PlanetSectorEvent::DISASTER,
                outputTable: [
                    3 => 1,
                    4 => 1,
                    5 => 1,
                ],
                tags: [PlanetSectorEventTagEnum::NEGATIVE],
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::NOTHING_TO_REPORT,
                eventName: PlanetSectorEvent::NOTHING_TO_REPORT,
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::NOTHING_TO_REPORT_NEGATIVE,
                eventName: PlanetSectorEvent::NOTHING_TO_REPORT,
                tags: [PlanetSectorEventTagEnum::NEGATIVE],
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::TIRED_2,
                eventName: PlanetSectorEvent::TIRED,
                outputTable: [2 => 1],
                tags: [PlanetSectorEventTagEnum::NEGATIVE],
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::OXYGEN_8,
                eventName: PlanetSectorEvent::OXYGEN,
                outputTable: [8 => 1],
                tags: [PlanetSectorEventTagEnum::NEGATIVE],
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::OXYGEN_16,
                eventName: PlanetSectorEvent::OXYGEN,
                outputTable: [16 => 1],
                tags: [PlanetSectorEventTagEnum::POSITIVE],
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::OXYGEN_24,
                eventName: PlanetSectorEvent::OXYGEN,
                outputTable: [24 => 1],
                tags: [PlanetSectorEventTagEnum::POSITIVE],
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::FUEL_1,
                eventName: PlanetSectorEvent::FUEL,
                outputTable: [1 => 1],
                tags: [PlanetSectorEventTagEnum::POSITIVE],
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::FUEL_2,
                eventName: PlanetSectorEvent::FUEL,
                outputTable: [2 => 1],
                tags: [PlanetSectorEventTagEnum::POSITIVE],
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::FUEL_3,
                eventName: PlanetSectorEvent::FUEL,
                outputTable: [3 => 1],
                tags: [PlanetSectorEventTagEnum::POSITIVE],
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::FUEL_3_NEGATIVE,
                eventName: PlanetSectorEvent::FUEL,
                outputTable: [3 => 1],
                tags: [PlanetSectorEventTagEnum::NEGATIVE],
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::FUEL_4,
                eventName: PlanetSectorEvent::FUEL,
                outputTable: [4 => 1],
                tags: [PlanetSectorEventTagEnum::POSITIVE],
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::FUEL_5,
                eventName: PlanetSectorEvent::FUEL,
                outputTable: [5 => 1],
                tags: [PlanetSectorEventTagEnum::POSITIVE],
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::FUEL_6,
                eventName: PlanetSectorEvent::FUEL,
                outputTable: [6 => 1],
                tags: [PlanetSectorEventTagEnum::POSITIVE],
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::ARTEFACT,
                eventName: PlanetSectorEvent::ARTEFACT,
                outputQuantity: [1 => 1],
                outputTable: [ItemEnum::ARTEFACT_GENERIC => 1],
                tags: [PlanetSectorEventTagEnum::POSITIVE],
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::KILL_RANDOM,
                eventName: PlanetSectorEvent::KILL_RANDOM,
                tags: [PlanetSectorEventTagEnum::NEGATIVE],
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::KILL_ALL,
                eventName: PlanetSectorEvent::KILL_ALL,
                tags: [PlanetSectorEventTagEnum::NEGATIVE],
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::PROVISION_1,
                eventName: PlanetSectorEvent::PROVISION,
                outputQuantity: [1 => 1],
                outputTable: [
                    'alien_steak' => 1,
                ],
                tags: [PlanetSectorEventTagEnum::POSITIVE],
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::PROVISION_2,
                eventName: PlanetSectorEvent::PROVISION,
                outputQuantity: [2 => 1],
                outputTable: [
                    'alien_steak' => 1,
                ],
                tags: [PlanetSectorEventTagEnum::POSITIVE],
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::PROVISION_3,
                eventName: PlanetSectorEvent::PROVISION,
                outputQuantity: [3 => 1],
                outputTable: [
                    'alien_steak' => 1,
                ],
                tags: [PlanetSectorEventTagEnum::POSITIVE],
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::PROVISION_4,
                eventName: PlanetSectorEvent::PROVISION,
                outputQuantity: [4 => 1],
                outputTable: [
                    'alien_steak' => 1,
                ],
                tags: [PlanetSectorEventTagEnum::POSITIVE],
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::FIGHT_CHABCHAB,
                eventName: PlanetSectorEvent::FIGHT,
                outputQuantity: [3 => 1, 4 => 1, 5 => 1], // loot amount
                outputTable: [GameRationEnum::ALIEN_STEAK => 1], // loot
                tags: [PlanetSectorEventTagEnum::NEGATIVE],
                fightStrength: 8
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::FIGHT_INSECT,
                eventName: PlanetSectorEvent::FIGHT,
                outputQuantity: [1 => 1], // loot amount
                outputTable: [GameFruitEnum::ALIEN_FRUIT_GENERIC => 1], // loot
                tags: [PlanetSectorEventTagEnum::NEGATIVE],
                fightStrength: 10
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::FIGHT_PREDATOR,
                eventName: PlanetSectorEvent::FIGHT,
                outputQuantity: [1 => 1], // loot amount
                outputTable: [GameRationEnum::ALIEN_STEAK => 1], // loot
                tags: [PlanetSectorEventTagEnum::NEGATIVE],
                fightStrength: 12
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::FIGHT_ALIEN,
                eventName: PlanetSectorEvent::FIGHT,
                outputQuantity: [1 => 1], // loot amount
                outputTable: [ItemEnum::ARTEFACT_GENERIC => 4, ItemEnum::ALIEN_BLASTER => 1], // loot
                tags: [PlanetSectorEventTagEnum::NEGATIVE],
                fightStrength: 12
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::FIGHT_ZOMBIES,
                eventName: PlanetSectorEvent::FIGHT,
                outputQuantity: [1 => 2, 2 => 1], // loot amount
                outputTable: [ItemEnum::ARTEFACT_GENERIC => 1], // loot
                tags: [PlanetSectorEventTagEnum::NEGATIVE],
                fightStrength: 15
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::FIGHT_MINDBLENDER,
                eventName: PlanetSectorEvent::FIGHT,
                outputQuantity: [1 => 1], // loot amount
                outputTable: [ItemEnum::STARMAP_FRAGMENT => 1], // loot
                tags: [PlanetSectorEventTagEnum::NEGATIVE],
                fightStrength: 18
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::FIGHT_MANKAROG,
                eventName: PlanetSectorEvent::FIGHT,
                outputQuantity: [3 => 1], // loot amount
                outputTable: [ItemEnum::ARTEFACT_GENERIC => 1], // loot
                tags: [PlanetSectorEventTagEnum::NEGATIVE, PlanetSectorEventTagEnum::REWARD_STARMAP_33], // Since we can't associate an amount to a specific reward, we use a special tag to do the 33% to get a starmap shard
                fightStrength: 32
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::FIGHT_WRECK,
                eventName: PlanetSectorEvent::FIGHT,
                outputQuantity: [1 => 1],
                outputTable: [ItemEnum::ALIEN_BLASTER => 1], // loot
                tags: [PlanetSectorEventTagEnum::NEGATIVE, PlanetSectorEventTagEnum::RANDOM_FIGHT],
                fightStrength: 0 // random
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::HARVEST_1,
                eventName: PlanetSectorEvent::HARVEST,
                outputQuantity: [1 => 1],
                outputTable: [GameFruitEnum::ALIEN_FRUIT_GENERIC => 1],
                tags: [PlanetSectorEventTagEnum::POSITIVE],
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::HARVEST_2,
                eventName: PlanetSectorEvent::HARVEST,
                outputQuantity: [2 => 1],
                outputTable: [GameFruitEnum::ALIEN_FRUIT_GENERIC => 1],
                tags: [PlanetSectorEventTagEnum::POSITIVE],
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::HARVEST_3,
                eventName: PlanetSectorEvent::HARVEST,
                outputQuantity: [3 => 1],
                outputTable: [GameFruitEnum::ALIEN_FRUIT_GENERIC => 1],
                tags: [PlanetSectorEventTagEnum::POSITIVE],
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::DISEASE,
                eventName: PlanetSectorEvent::DISEASE,
                tags: [PlanetSectorEventTagEnum::NEGATIVE],
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::STARMAP,
                eventName: PlanetSectorEvent::STARMAP,
                outputQuantity: [1 => 1],
                outputTable: [
                    'starmap_fragment' => 1,
                ],
                tags: [PlanetSectorEventTagEnum::POSITIVE],
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::MUSH_TRAP,
                eventName: PlanetSectorEvent::MUSH_TRAP,
                outputQuantity: [50 => 1], // odds (percentage)
                tags: [PlanetSectorEventTagEnum::NEGATIVE],
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::AGAIN,
                eventName: PlanetSectorEvent::AGAIN,
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::ITEM_LOST,
                eventName: PlanetSectorEvent::ITEM_LOST,
                tags: [PlanetSectorEventTagEnum::NEGATIVE],
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::BACK,
                eventName: PlanetSectorEvent::BACK,
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::PLAYER_LOST,
                eventName: PlanetSectorEvent::PLAYER_LOST,
                tags: [PlanetSectorEventTagEnum::NEGATIVE],
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::FIND_LOST,
                eventName: PlanetSectorEvent::FIND_LOST,
                tags: [PlanetSectorEventTagEnum::POSITIVE],
            ),
            new PlanetSectorEventConfigDto(
                name: PlanetSectorEvent::KILL_LOST,
                eventName: PlanetSectorEvent::KILL_LOST,
                tags: [PlanetSectorEventTagEnum::NEGATIVE],
            ),
        ];
    }
}
