<?php

namespace Mush\Disease\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Disease\Entity\DiseaseCause;
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

        /** @var DiseaseCause $spoiledFood */
        $spoiledFood = $this->getReference(DiseaseCauseEnum::SPOILED_FOOD);

        $acidReflux = new DiseaseConfig();
        $acidReflux->setGameConfig($gameConfig);
        $acidReflux->setName(DiseaseEnum::ACID_REFLUX);
        $acidReflux->setCauses(new ArrayCollection([
            $spoiledFood,
        ]));

        $manager->persist($acidReflux);

        $foodPoisoning = new DiseaseConfig();
        $foodPoisoning->setGameConfig($gameConfig);
        $foodPoisoning->setName(DiseaseEnum::FOOD_POISONING);
        $foodPoisoning->setCauses(new ArrayCollection([
            $spoiledFood,
        ]));

        $manager->persist($foodPoisoning);

        $tapeworm = new DiseaseConfig();
        $tapeworm->setGameConfig($gameConfig);
        $tapeworm->setName(DiseaseEnum::TAPEWORM);
        $tapeworm->setCauses(new ArrayCollection([
            $spoiledFood,
        ]));

        $manager->persist($tapeworm);

        $gastroenteritis = new DiseaseConfig();
        $gastroenteritis->setGameConfig($gameConfig);
        $gastroenteritis->setName(DiseaseEnum::GASTROENTERITIS);
        $gastroenteritis->setCauses(new ArrayCollection([
            $spoiledFood,
        ]));

        $manager->persist($gastroenteritis);

        $manager->flush();

        $this->addReference(DiseaseEnum::ACID_REFLUX, $acidReflux);
        $this->addReference(DiseaseEnum::FOOD_POISONING, $foodPoisoning);
        $this->addReference(DiseaseEnum::TAPEWORM, $tapeworm);
        $this->addReference(DiseaseEnum::GASTROENTERITIS, $gastroenteritis);
    }

    public function getDependencies()
    {
        return [
            GameConfigFixtures::class,
            DiseaseCauseFixtures::class,
        ];
    }
}
