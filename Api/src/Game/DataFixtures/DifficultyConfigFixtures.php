<?php

namespace Mush\Game\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Entity\DifficultyConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameConfigEnum;

class DifficultyConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        $difficultyConfig = new DifficultyConfig();

        $difficultyConfig
            ->setName(GameConfigEnum::DEFAULT)
            ->setEquipmentBreakRate(30)
            ->setDoorBreakRate(40)
            ->setEquipmentFireBreakRate(30)
            ->setStartingFireRate(2)
            ->setPropagatingFireRate(30)
            ->setHullFireDamageRate(20)
            ->setTremorRate(5)
            ->setMetalPlateRate(5)
            ->setElectricArcRate(5)
            ->setPanicCrisisRate(5)
            ->setFireHullDamage([2 => 1, 4 => 1])
            ->setFirePlayerDamage([2 => 1])
            ->setElectricArcPlayerDamage([3 => 1])
            ->setTremorPlayerDamage([1 => 1, 2 => 1, 3 => 1])
            ->setMetalPlatePlayerDamage([4 => 1, 5 => 1, 6 => 1])
            ->setPanicCrisisPlayerDamage([3 => 1])
            ->setPlantDiseaseRate(5)
            ->setCycleDiseaseRate(20)
            ->setEquipmentBreakRateDistribution([
                EquipmentEnum::BIOS_TERMINAL => 3,
                EquipmentEnum::COMMUNICATION_CENTER => 6,
                EquipmentEnum::NERON_CORE => 6,
                EquipmentEnum::RESEARCH_LABORATORY => 6,
                EquipmentEnum::CALCULATOR => 6,
                EquipmentEnum::EMERGENCY_REACTOR => 6,
                EquipmentEnum::REACTOR_LATERAL => 6,
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
        ;

        $manager->persist($difficultyConfig);

        $gameConfig->setDifficultyConfig($difficultyConfig);
        $manager->persist($gameConfig);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
