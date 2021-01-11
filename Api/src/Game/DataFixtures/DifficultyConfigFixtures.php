<?php

namespace Mush\Game\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Game\Entity\DifficultyConfig;
use Mush\Game\Entity\GameConfig;

class DifficultyConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        $difficultyConfig = new DifficultyConfig();

        $difficultyConfig
            ->setGameConfig($gameConfig)
            ->setEquipmentBreakRate(30)
            ->setDoorBreakRate(40)
            ->setEquipmentFireBreakRate(40)
            ->setStartingFireRate(10)
            ->setPropagatingFireRate(50)
            ->setTremorRate(5)
            ->setMetalPlateRate(5)
            ->setElectricArcRate(5)
            ->setPanicCrisisRate(5)
        ;

        $manager->persist($difficultyConfig);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
