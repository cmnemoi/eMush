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
            'weightAtPlanetGeneration' => 0,
            'weightAtPlanetAnalysis' => 0,
            'weightAtPlanetExploration' => 0,
            'maxPerPlanet' => 0,
            'explorationEvents' => [
                PlanetSectorEvent::NOTHING_TO_REPORT => 4,
                PlanetSectorEvent::TIRED => 3,
                PlanetSectorEvent::ACCIDENT => 2,
                PlanetSectorEvent::DISASTER => 1,
            ],
        ],
        [
            'name' => PlanetSectorEnum::FOREST . '_default',
            'sectorName' => PlanetSectorEnum::FOREST,
            'weightAtPlanetGeneration' => 8,
            'weightAtPlanetAnalysis' => 12,
            'weightAtPlanetExploration' => 8,
            'maxPerPlanet' => 4,
            'explorationEvents' => [
                PlanetSectorEvent::NOTHING_TO_REPORT => 1,
            ],
        ],
        [
            'name' => PlanetSectorEnum::MOUNTAIN . '_default',
            'sectorName' => PlanetSectorEnum::MOUNTAIN,
            'weightAtPlanetGeneration' => 8,
            'weightAtPlanetAnalysis' => 8,
            'weightAtPlanetExploration' => 8,
            'maxPerPlanet' => 4,
            'explorationEvents' => [
                PlanetSectorEvent::ACCIDENT => 4,
                PlanetSectorEvent::TIRED => 2,
                PlanetSectorEvent::NOTHING_TO_REPORT => 1,
            ],
        ],
        [
            'name' => PlanetSectorEnum::SWAMP . '_default',
            'sectorName' => PlanetSectorEnum::SWAMP,
            'weightAtPlanetGeneration' => 8,
            'weightAtPlanetAnalysis' => 8,
            'weightAtPlanetExploration' => 8,
            'maxPerPlanet' => 4,
            'explorationEvents' => [
                PlanetSectorEvent::TIRED => 2,
                PlanetSectorEvent::NOTHING_TO_REPORT => 1,
            ],
        ],
        [
            'name' => PlanetSectorEnum::DESERT . '_default',
            'sectorName' => PlanetSectorEnum::DESERT,
            'weightAtPlanetGeneration' => 12,
            'weightAtPlanetAnalysis' => 12,
            'weightAtPlanetExploration' => 8,
            'maxPerPlanet' => 4,
            'explorationEvents' => [
                PlanetSectorEvent::TIRED => 3,
                PlanetSectorEvent::NOTHING_TO_REPORT => 3,
            ],
        ],
        [
            'name' => PlanetSectorEnum::OCEAN . '_default',
            'sectorName' => PlanetSectorEnum::OCEAN,
            'weightAtPlanetGeneration' => 8,
            'weightAtPlanetAnalysis' => 12,
            'weightAtPlanetExploration' => 8,
            'maxPerPlanet' => 4,
            'explorationEvents' => [
                PlanetSectorEvent::NOTHING_TO_REPORT => 6,
            ],
        ],
        [
            'name' => PlanetSectorEnum::CAVE . '_default',
            'sectorName' => PlanetSectorEnum::CAVE,
            'weightAtPlanetGeneration' => 4,
            'weightAtPlanetAnalysis' => 2,
            'weightAtPlanetExploration' => 8,
            'maxPerPlanet' => 4,
            'explorationEvents' => [
                PlanetSectorEvent::ACCIDENT => 3,
                PlanetSectorEvent::NOTHING_TO_REPORT => 1,
            ],
        ],
        [
            'name' => PlanetSectorEnum::RUMINANT . '_default',
            'sectorName' => PlanetSectorEnum::RUMINANT,
            'weightAtPlanetGeneration' => 8,
            'weightAtPlanetAnalysis' => 4,
            'weightAtPlanetExploration' => 8,
            'maxPerPlanet' => 4,
            'explorationEvents' => [
                PlanetSectorEvent::ACCIDENT => 2,
                PlanetSectorEvent::NOTHING_TO_REPORT => 1,
            ],
        ],
        [
            'name' => PlanetSectorEnum::PREDATOR . '_default',
            'sectorName' => PlanetSectorEnum::PREDATOR,
            'weightAtPlanetGeneration' => 8,
            'weightAtPlanetAnalysis' => 2,
            'weightAtPlanetExploration' => 6,
            'maxPerPlanet' => 4,
            'explorationEvents' => [
                PlanetSectorEvent::ACCIDENT => 3,
                PlanetSectorEvent::NOTHING_TO_REPORT => 2,
            ],
        ],
        [
            'name' => PlanetSectorEnum::INTELLIGENT . '_default',
            'sectorName' => PlanetSectorEnum::INTELLIGENT,
            'weightAtPlanetGeneration' => 8,
            'weightAtPlanetAnalysis' => 4,
            'weightAtPlanetExploration' => 8,
            'maxPerPlanet' => 4,
            'explorationEvents' => [
                PlanetSectorEvent::NOTHING_TO_REPORT => 1,
            ],
        ],
        [
            'name' => PlanetSectorEnum::INSECT . '_default',
            'sectorName' => PlanetSectorEnum::INSECT,
            'weightAtPlanetGeneration' => 10,
            'weightAtPlanetAnalysis' => 2,
            'weightAtPlanetExploration' => 8,
            'maxPerPlanet' => 4,
            'explorationEvents' => [
                PlanetSectorEvent::ACCIDENT => 4,
                PlanetSectorEvent::NOTHING_TO_REPORT => 1,
            ],
        ],
        [
            'name' => PlanetSectorEnum::MANKAROG . '_default',
            'sectorName' => PlanetSectorEnum::MANKAROG,
            'weightAtPlanetGeneration' => 2,
            'weightAtPlanetAnalysis' => 4,
            'weightAtPlanetExploration' => 6,
            'maxPerPlanet' => 1,
            'explorationEvents' => [
                PlanetSectorEvent::NOTHING_TO_REPORT => 1,
            ],
        ],
        [
            'name' => PlanetSectorEnum::HYDROCARBON . '_default',
            'sectorName' => PlanetSectorEnum::HYDROCARBON,
            'weightAtPlanetGeneration' => 5,
            'weightAtPlanetAnalysis' => 2,
            'weightAtPlanetExploration' => 8,
            'maxPerPlanet' => 2,
            'explorationEvents' => [
                PlanetSectorEvent::FUEL => 1,
            ],
        ],
        [
            'name' => PlanetSectorEnum::OXYGEN . '_default',
            'sectorName' => PlanetSectorEnum::OXYGEN,
            'weightAtPlanetGeneration' => 8,
            'weightAtPlanetAnalysis' => 12,
            'weightAtPlanetExploration' => 8,
            'maxPerPlanet' => 1,
            'explorationEvents' => [
                PlanetSectorEvent::OXYGEN => 9,
                PlanetSectorEvent::NOTHING_TO_REPORT => 1,
            ],
        ],
        [
            'name' => PlanetSectorEnum::COLD . '_default',
            'sectorName' => PlanetSectorEnum::COLD,
            'weightAtPlanetGeneration' => 8,
            'weightAtPlanetAnalysis' => 8,
            'weightAtPlanetExploration' => 8,
            'maxPerPlanet' => 4,
            'explorationEvents' => [
                PlanetSectorEvent::NOTHING_TO_REPORT => 4,
                PlanetSectorEvent::TIRED => 3,
                PlanetSectorEvent::ACCIDENT => 1,
            ],
        ],
        [
            'name' => PlanetSectorEnum::HOT . '_default',
            'sectorName' => PlanetSectorEnum::HOT,
            'weightAtPlanetGeneration' => 8,
            'weightAtPlanetAnalysis' => 8,
            'weightAtPlanetExploration' => 8,
            'maxPerPlanet' => 4,
            'explorationEvents' => [
                PlanetSectorEvent::TIRED => 4,
                PlanetSectorEvent::NOTHING_TO_REPORT => 3,
                PlanetSectorEvent::ACCIDENT => 1,
            ],
        ],
        [
            'name' => PlanetSectorEnum::STRONG_WIND . '_default',
            'sectorName' => PlanetSectorEnum::STRONG_WIND,
            'weightAtPlanetGeneration' => 8,
            'weightAtPlanetAnalysis' => 8,
            'weightAtPlanetExploration' => 8,
            'maxPerPlanet' => 4,
            'explorationEvents' => [
                PlanetSectorEvent::NOTHING_TO_REPORT => 6,
                PlanetSectorEvent::TIRED => 3,
            ],
        ],
        [
            'name' => PlanetSectorEnum::SISMIC_ACTIVITY . '_default',
            'sectorName' => PlanetSectorEnum::SISMIC_ACTIVITY,
            'weightAtPlanetGeneration' => 3,
            'weightAtPlanetAnalysis' => 8,
            'weightAtPlanetExploration' => 8,
            'maxPerPlanet' => 4,
            'explorationEvents' => [
                PlanetSectorEvent::NOTHING_TO_REPORT => 4,
                PlanetSectorEvent::ACCIDENT => 2,
            ],
        ],
        [
            'name' => PlanetSectorEnum::VOLCANIC_ACTIVITY . '_default',
            'sectorName' => PlanetSectorEnum::VOLCANIC_ACTIVITY,
            'weightAtPlanetGeneration' => 3,
            'weightAtPlanetAnalysis' => 8,
            'weightAtPlanetExploration' => 6,
            'maxPerPlanet' => 4,
            'explorationEvents' => [
                PlanetSectorEvent::NOTHING_TO_REPORT => 6,
            ],
        ],
        [
            'name' => PlanetSectorEnum::RUINS . '_default',
            'sectorName' => PlanetSectorEnum::RUINS,
            'weightAtPlanetGeneration' => 2,
            'weightAtPlanetAnalysis' => 2,
            'weightAtPlanetExploration' => 8,
            'maxPerPlanet' => 4,
            'explorationEvents' => [
                PlanetSectorEvent::NOTHING_TO_REPORT => 3,
                PlanetSectorEvent::ACCIDENT => 1,
            ],
        ],
        [
            'name' => PlanetSectorEnum::WRECK . '_default',
            'sectorName' => PlanetSectorEnum::WRECK,
            'weightAtPlanetGeneration' => 2,
            'weightAtPlanetAnalysis' => 1,
            'weightAtPlanetExploration' => 8,
            'maxPerPlanet' => 4,
            'explorationEvents' => [
                PlanetSectorEvent::NOTHING_TO_REPORT => 2,
            ],
        ],
        [
            'name' => PlanetSectorEnum::FRUIT_TREES . '_default',
            'sectorName' => PlanetSectorEnum::FRUIT_TREES,
            'weightAtPlanetGeneration' => 3,
            'weightAtPlanetAnalysis' => 1,
            'weightAtPlanetExploration' => 8,
            'maxPerPlanet' => 4,
            'explorationEvents' => [
                PlanetSectorEvent::NOTHING_TO_REPORT => 3,
            ],
        ],
        [
            'name' => PlanetSectorEnum::LOST . '_default',
            'sectorName' => PlanetSectorEnum::LOST,
            'weightAtPlanetGeneration' => 0,
            'weightAtPlanetAnalysis' => 0,
            'weightAtPlanetExploration' => 8,
            'maxPerPlanet' => 0,
            'explorationEvents' => [
                PlanetSectorEvent::FIND_LOST => 7,
                PlanetSectorEvent::AGAIN => 2,
                PlanetSectorEvent::KILL_LOST => 1,
            ],
        ],
        [
            'name' => PlanetSectorEnum::CRISTAL_FIELD . '_default',
            'sectorName' => PlanetSectorEnum::CRISTAL_FIELD,
            'weightAtPlanetGeneration' => 2,
            'weightAtPlanetAnalysis' => 4,
            'weightAtPlanetExploration' => 10,
            'maxPerPlanet' => 1,
            'explorationEvents' => [
                PlanetSectorEvent::ACCIDENT => 1,
                PlanetSectorEvent::NOTHING_TO_REPORT => 1,
            ],
        ],
    ];
}
