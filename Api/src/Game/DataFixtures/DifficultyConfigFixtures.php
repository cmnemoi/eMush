<?php

namespace Mush\Game\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Entity\DifficultyConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\DifficultyEnum;
use Mush\Game\Enum\GameConfigEnum;

class DifficultyConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public const string DEFAULT_DIFFICULTY_CONFIG = 'default_difficulty_config';

    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        $difficultyConfig = new DifficultyConfig();

        $difficultyConfig
            ->setName('difficultyConfig_' . GameConfigEnum::TEST)
            ->setEquipmentBreakRate(0)
            ->setDoorBreakRate(0)
            ->setEquipmentFireBreakRate(0)
            ->setStartingFireRate(2)
            ->setPropagatingFireRate(0)
            ->setMaximumAllowedSpreadingFires(2)
            ->setHullFireDamageRate(0)
            ->setTremorRate(0)
            ->setMetalPlateRate(0)
            ->setElectricArcRate(0)
            ->setPanicCrisisRate(0)
            ->setFireHullDamage([2 => 1, 4 => 1])
            ->setFirePlayerDamage([2 => 1])
            ->setElectricArcPlayerDamage([3 => 1])
            ->setTremorPlayerDamage([1 => 1, 2 => 1, 3 => 1])
            ->setMetalPlatePlayerDamage([4 => 1, 5 => 1, 6 => 1])
            ->setPanicCrisisPlayerDamage([3 => 1])
            ->setPlantDiseaseRate(0)
            ->setCycleDiseaseRate(0)
            ->setEquipmentBreakRateDistribution([
                EquipmentEnum::BIOS_TERMINAL => 3,
                EquipmentEnum::COMMUNICATION_CENTER => 6,
                EquipmentEnum::NERON_CORE => 6,
                EquipmentEnum::RESEARCH_LABORATORY => 6,
                EquipmentEnum::CALCULATOR => 6,
                EquipmentEnum::EMERGENCY_REACTOR => 6,
                EquipmentEnum::REACTOR_LATERAL_ALPHA => 6,
                EquipmentEnum::REACTOR_LATERAL_BRAVO => 6,
                EquipmentEnum::GRAVITY_SIMULATOR => 6,
                EquipmentEnum::ASTRO_TERMINAL => 12,
                EquipmentEnum::COMMAND_TERMINAL => 12,
                EquipmentEnum::PLANET_SCANNER => 12,
                EquipmentEnum::JUKEBOX => 12,
                EquipmentEnum::ANTENNA => 12,
                EquipmentEnum::PATROL_SHIP => 12,
                EquipmentEnum::PASIPHAE => 12,
                EquipmentEnum::COMBUSTION_CHAMBER => 12,
                EquipmentEnum::KITCHEN => 12,
                EquipmentEnum::DYNARCADE => 12,
                EquipmentEnum::COFFEE_MACHINE => 12,
                EquipmentEnum::MYCOSCAN => 12,
                EquipmentEnum::TURRET_COMMAND => 12,
                EquipmentEnum::SURGERY_PLOT => 12,
                EquipmentEnum::THALASSO => 25,
                EquipmentEnum::CAMERA_EQUIPMENT => 25,
                EquipmentEnum::SHOWER => 25,
                EquipmentEnum::FUEL_TANK => 25,
                EquipmentEnum::OXYGEN_TANK => 25,
            ])
            ->setDifficultyModes([
                DifficultyEnum::NORMAL => 1,
                DifficultyEnum::HARD => 5,
                DifficultyEnum::VERY_HARD => 10,
            ])
            ->setHunterSpawnRate(0)
            ->setHunterSafeCycles([2, 3])
            ->setStartingHuntersNumberOfTruceCycles(2)
        ;

        $manager->persist($difficultyConfig);

        $gameConfig->setDifficultyConfig($difficultyConfig);
        $manager->persist($gameConfig);

        $manager->flush();

        $this->addReference(self::DEFAULT_DIFFICULTY_CONFIG, $difficultyConfig);
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
