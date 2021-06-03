<?php

namespace Mush\Disease\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Disease\Entity\DiseaseConfig;
use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;

class DiseaseConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        $foodPoisoning = new DiseaseConfig();
        $foodPoisoning->setGameConfig($gameConfig);
        $foodPoisoning->setName(DiseaseEnum::FOOD_POISONING);
        $foodPoisoning->setCauses([
            DiseaseCauseEnum::PERISHED_FOOD,
        ]);

        $manager->persist($foodPoisoning);

        $manager->flush();

        $this->addReference(DiseaseEnum::FOOD_POISONING, $foodPoisoning);
    }

    public function getDependencies()
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
