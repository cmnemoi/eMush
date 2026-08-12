<?php

declare(strict_types=1);

namespace Mush\Exploration\ConfigData;

use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Exploration\Event\PlanetSectorEvent;

/** @codeCoverageIgnore */
final class PlanetSectorConfigData
{
    public static array $dataArray = [
        [
            'name' => PlanetSectorEnum::LANDING . '_default',
            'sectorName' => PlanetSectorEnum::LANDING,
            'weightAtPlanetAnalysis' => 0,
            'weightAtPlanetExploration' => 0,
            'explorationEvents' => [
                PlanetSectorEvent::NOTHING_TO_REPORT => 4,
                PlanetSectorEvent::TIRED_2 => 3,
                PlanetSectorEvent::ACCIDENT_3_5 => 2,
                PlanetSectorEvent::DISASTER_3_5 => 1,
            ],
        ],
        [
            'name' => PlanetSectorEnum::FOREST . '_default',
            'sectorName' => PlanetSectorEnum::FOREST,
            'weightAtPlanetAnalysis' => 12,
            'weightAtPlanetExploration' => 8,
            'explorationEvents' => [
                PlanetSectorEvent::HARVEST_2 => 4,
                PlanetSectorEvent::AGAIN => 3,
                PlanetSectorEvent::DISEASE => 2,
                PlanetSectorEvent::PLAYER_LOST => 1,
            ],
        ],
        [
            'name' => PlanetSectorEnum::MOUNTAIN . '_default',
            'sectorName' => PlanetSectorEnum::MOUNTAIN,
            'weightAtPlanetAnalysis' => 8,
            'weightAtPlanetExploration' => 8,
            'explorationEvents' => [
                PlanetSectorEvent::ACCIDENT_3_5 => 4,
                PlanetSectorEvent::FUEL_1 => 3,
                PlanetSectorEvent::TIRED_2 => 2,
                PlanetSectorEvent::HARVEST_1 => 1,
            ],
        ],
        [
            'name' => PlanetSectorEnum::SWAMP . '_default',
            'sectorName' => PlanetSectorEnum::SWAMP,
            'weightAtPlanetAnalysis' => 8,
            'weightAtPlanetExploration' => 8,
            'explorationEvents' => [
                PlanetSectorEvent::DISEASE => 4,
                PlanetSectorEvent::HARVEST_2 => 3,
                PlanetSectorEvent::TIRED_2 => 2,
                PlanetSectorEvent::NOTHING_TO_REPORT => 1,
            ],
        ],
        [
            'name' => PlanetSectorEnum::DESERT . '_default',
            'sectorName' => PlanetSectorEnum::DESERT,
            'weightAtPlanetAnalysis' => 12,
            'weightAtPlanetExploration' => 8,
            'explorationEvents' => [
                PlanetSectorEvent::NOTHING_TO_REPORT => 5,
                PlanetSectorEvent::TIRED_2 => 4,
                PlanetSectorEvent::AGAIN => 1,
            ],
        ],
        [
            'name' => PlanetSectorEnum::OCEAN . '_default',
            'sectorName' => PlanetSectorEnum::OCEAN,
            'weightAtPlanetAnalysis' => 12,
            'weightAtPlanetExploration' => 8,
            'explorationEvents' => [
                PlanetSectorEvent::NOTHING_TO_REPORT => 7,
                PlanetSectorEvent::PROVISION_3_FISH => 2,
                PlanetSectorEvent::PLAYER_LOST => 1,
            ],
        ],
        [
            'name' => PlanetSectorEnum::CAVE . '_default',
            'sectorName' => PlanetSectorEnum::CAVE,
            'weightAtPlanetAnalysis' => 2,
            'weightAtPlanetExploration' => 8,
            'explorationEvents' => [
                PlanetSectorEvent::FUEL_2 => 4,
                PlanetSectorEvent::ACCIDENT_3_5 => 3,
                PlanetSectorEvent::AGAIN => 2,
                PlanetSectorEvent::ARTEFACT => 1,
            ],
        ],
        [
            'name' => PlanetSectorEnum::RUMINANT . '_default',
            'sectorName' => PlanetSectorEnum::RUMINANT,
            'weightAtPlanetAnalysis' => 4,
            'weightAtPlanetExploration' => 8,
            'explorationEvents' => [
                PlanetSectorEvent::PROVISION_4_STEAK => 4,
                PlanetSectorEvent::PROVISION_2_STEAK => 3,
                PlanetSectorEvent::ACCIDENT_3_5 => 2,
                PlanetSectorEvent::FIGHT_CHABCHAB => 1,
            ],
        ],
        [
            'name' => PlanetSectorEnum::PREDATOR . '_default',
            'sectorName' => PlanetSectorEnum::PREDATOR,
            'weightAtPlanetAnalysis' => 2,
            'weightAtPlanetExploration' => 6,
            'explorationEvents' => [
                PlanetSectorEvent::FIGHT_PREDATOR => 4,
                PlanetSectorEvent::ACCIDENT_3_5 => 3,
                PlanetSectorEvent::NOTHING_TO_REPORT => 2,
                PlanetSectorEvent::PROVISION_3_STEAK => 1,
            ],
        ],
        [
            'name' => PlanetSectorEnum::INTELLIGENT . '_default',
            'sectorName' => PlanetSectorEnum::INTELLIGENT,
            'weightAtPlanetAnalysis' => 4,
            'weightAtPlanetExploration' => 8,
            'explorationEvents' => [
                PlanetSectorEvent::FIGHT_ALIEN => 4,
                PlanetSectorEvent::PROVISION_2_STEAK => 1,
                PlanetSectorEvent::PROVISION_2_FISH => 1,
                PlanetSectorEvent::PROVISION_2_INSECT => 1,
                PlanetSectorEvent::ARTEFACT => 2,
                PlanetSectorEvent::ITEM_LOST => 1,
            ],
        ],
        [
            'name' => PlanetSectorEnum::INSECT . '_default',
            'sectorName' => PlanetSectorEnum::INSECT,
            'weightAtPlanetAnalysis' => 2,
            'weightAtPlanetExploration' => 8,
            'explorationEvents' => [
                PlanetSectorEvent::ACCIDENT_3_5 => 4,
                PlanetSectorEvent::DISEASE => 3,
                PlanetSectorEvent::PROVISION_1_INSECT => 2,
                PlanetSectorEvent::FIGHT_INSECT => 1,
            ],
        ],
        [
            'name' => PlanetSectorEnum::MANKAROG . '_default',
            'sectorName' => PlanetSectorEnum::MANKAROG,
            'weightAtPlanetAnalysis' => 4,
            'weightAtPlanetExploration' => 6,
            'explorationEvents' => [
                PlanetSectorEvent::KILL_RANDOM => 4,
                PlanetSectorEvent::FIGHT_MANKAROG => 3,
                PlanetSectorEvent::BACK => 2,
                PlanetSectorEvent::ARTEFACT => 1,
            ],
        ],
        [
            'name' => PlanetSectorEnum::HYDROCARBON . '_default',
            'sectorName' => PlanetSectorEnum::HYDROCARBON,
            'weightAtPlanetAnalysis' => 2,
            'weightAtPlanetExploration' => 8,
            'explorationEvents' => [
                PlanetSectorEvent::FUEL_3_NEGATIVE => 4,
                PlanetSectorEvent::FUEL_4 => 3,
                PlanetSectorEvent::FUEL_5 => 2,
                PlanetSectorEvent::FUEL_6 => 1,
            ],
        ],
        [
            'name' => PlanetSectorEnum::OXYGEN . '_default',
            'sectorName' => PlanetSectorEnum::OXYGEN,
            'weightAtPlanetAnalysis' => 12,
            'weightAtPlanetExploration' => 8,
            'explorationEvents' => [
                PlanetSectorEvent::OXYGEN_24 => 4,
                PlanetSectorEvent::OXYGEN_16 => 3,
                PlanetSectorEvent::OXYGEN_8 => 2,
                PlanetSectorEvent::NOTHING_TO_REPORT_NEGATIVE => 1,
            ],
        ],
        [
            'name' => PlanetSectorEnum::COLD . '_default',
            'sectorName' => PlanetSectorEnum::COLD,
            'weightAtPlanetAnalysis' => 8,
            'weightAtPlanetExploration' => 8,
            'explorationEvents' => [
                PlanetSectorEvent::NOTHING_TO_REPORT => 4,
                PlanetSectorEvent::TIRED_2 => 3,
                PlanetSectorEvent::PLAYER_LOST => 2,
                PlanetSectorEvent::ACCIDENT_3_5 => 1,
            ],
        ],
        [
            'name' => PlanetSectorEnum::HOT . '_default',
            'sectorName' => PlanetSectorEnum::HOT,
            'weightAtPlanetAnalysis' => 8,
            'weightAtPlanetExploration' => 8,
            'explorationEvents' => [
                PlanetSectorEvent::TIRED_2 => 4,
                PlanetSectorEvent::NOTHING_TO_REPORT => 3,
                PlanetSectorEvent::HARVEST_2 => 2,
                PlanetSectorEvent::ACCIDENT_3_5 => 1,
            ],
        ],
        [
            'name' => PlanetSectorEnum::STRONG_WIND . '_default',
            'sectorName' => PlanetSectorEnum::STRONG_WIND,
            'weightAtPlanetAnalysis' => 8,
            'weightAtPlanetExploration' => 8,
            'explorationEvents' => [
                PlanetSectorEvent::NOTHING_TO_REPORT => 6,
                PlanetSectorEvent::TIRED_2 => 3,
                PlanetSectorEvent::ITEM_LOST => 1,
            ],
        ],
        [
            'name' => PlanetSectorEnum::SEISMIC_ACTIVITY . '_default',
            'sectorName' => PlanetSectorEnum::SEISMIC_ACTIVITY,
            'weightAtPlanetAnalysis' => 8,
            'weightAtPlanetExploration' => 8,
            'explorationEvents' => [
                PlanetSectorEvent::NOTHING_TO_REPORT => 4,
                PlanetSectorEvent::BACK => 3,
                PlanetSectorEvent::ACCIDENT_3_5 => 2,
                PlanetSectorEvent::KILL_RANDOM => 1,
            ],
        ],
        [
            'name' => PlanetSectorEnum::VOLCANIC_ACTIVITY . '_default',
            'sectorName' => PlanetSectorEnum::VOLCANIC_ACTIVITY,
            'weightAtPlanetAnalysis' => 8,
            'weightAtPlanetExploration' => 6,
            'explorationEvents' => [
                PlanetSectorEvent::NOTHING_TO_REPORT => 7,
                PlanetSectorEvent::BACK => 2,
                PlanetSectorEvent::KILL_ALL => 1,
            ],
        ],
        [
            'name' => PlanetSectorEnum::RUINS . '_default',
            'sectorName' => PlanetSectorEnum::RUINS,
            'weightAtPlanetAnalysis' => 2,
            'weightAtPlanetExploration' => 8,
            'explorationEvents' => [
                PlanetSectorEvent::ARTEFACT => 4,
                PlanetSectorEvent::NOTHING_TO_REPORT => 3,
                PlanetSectorEvent::FIGHT_ZOMBIES => 2,
                PlanetSectorEvent::ACCIDENT_3_5 => 1,
            ],
        ],
        [
            'name' => PlanetSectorEnum::WRECK . '_default',
            'sectorName' => PlanetSectorEnum::WRECK,
            'weightAtPlanetAnalysis' => 1,
            'weightAtPlanetExploration' => 8,
            'explorationEvents' => [
                PlanetSectorEvent::ARTEFACT => 4,
                PlanetSectorEvent::FUEL_3 => 3,
                PlanetSectorEvent::NOTHING_TO_REPORT => 2,
                PlanetSectorEvent::FIGHT_WRECK => 1,
            ],
        ],
        [
            'name' => PlanetSectorEnum::FRUIT_TREES . '_default',
            'sectorName' => PlanetSectorEnum::FRUIT_TREES,
            'weightAtPlanetAnalysis' => 1,
            'weightAtPlanetExploration' => 8,
            'explorationEvents' => [
                PlanetSectorEvent::HARVEST_3 => 4,
                PlanetSectorEvent::HARVEST_1 => 3,
                PlanetSectorEvent::NOTHING_TO_REPORT_NEGATIVE => 3,
            ],
        ],
        [
            'name' => PlanetSectorEnum::LOST . '_default',
            'sectorName' => PlanetSectorEnum::LOST,
            'weightAtPlanetAnalysis' => 0,
            'weightAtPlanetExploration' => 8,
            'explorationEvents' => [
                PlanetSectorEvent::FIND_LOST => 7,
                PlanetSectorEvent::AGAIN => 2,
                PlanetSectorEvent::KILL_LOST => 1,
            ],
        ],
        [
            'name' => PlanetSectorEnum::TREASURE_HUNT_PET . '_default',
            'sectorName' => PlanetSectorEnum::TREASURE_HUNT_PET,
            'weightAtPlanetAnalysis' => 0,
            'weightAtPlanetExploration' => 0,
            'explorationEvents' => ['' => 0],
        ],
        [
            'name' => PlanetSectorEnum::TREASURE_HUNT_SHIP . '_default',
            'sectorName' => PlanetSectorEnum::TREASURE_HUNT_SHIP,
            'weightAtPlanetAnalysis' => 0,
            'weightAtPlanetExploration' => 0,
            'explorationEvents' => ['' => 0],
        ],
        [
            'name' => PlanetSectorEnum::CRISTAL_FIELD . '_default',
            'sectorName' => PlanetSectorEnum::CRISTAL_FIELD,
            'weightAtPlanetAnalysis' => 4,
            'weightAtPlanetExploration' => 10,
            'explorationEvents' => [
                PlanetSectorEvent::MUSH_TRAP => 3,
                PlanetSectorEvent::STARMAP => 3,
                PlanetSectorEvent::FIGHT_MINDBLENDER => 3,
                PlanetSectorEvent::PLAYER_LOST => 1,
            ],
        ],
    ];
}
