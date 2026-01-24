<?php

namespace Mush\Disease\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Disease\ConfigData\ConsumableDiseaseConfigData;
use Mush\Disease\Entity\Config\ConsumableDiseaseConfig;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;

class ConsumableDiseaseConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        foreach (ConsumableDiseaseConfigData::$dataArray as $consumableDiseaseConfigData) {
            $consumableDiseaseConfig = new ConsumableDiseaseConfig();

            $consumableDiseaseConfig
                ->setName($consumableDiseaseConfigData['name'])
                ->setCauseName($consumableDiseaseConfigData['causeName'])
                ->setDiseasesName($consumableDiseaseConfigData['diseasesName'])
                ->setCuresName($consumableDiseaseConfigData['curesName'])
                ->setDiseasesChances($consumableDiseaseConfigData['diseasesChances'])
                ->setCuresChances($consumableDiseaseConfigData['curesChances'])
                ->setDiseasesDelayMin($consumableDiseaseConfigData['diseasesDelayMin'])
                ->setDiseasesDelayLength($consumableDiseaseConfigData['diseasesDelayLength'])
                ->setEffectNumber($consumableDiseaseConfigData['effectNumber']);

            $manager->persist($consumableDiseaseConfig);
            $gameConfig->addConsumableDiseaseConfig($consumableDiseaseConfig);
        }
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
