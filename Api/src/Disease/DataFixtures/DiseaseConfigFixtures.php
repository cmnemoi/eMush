<?php

namespace Mush\Disease\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Disease\ConfigData\DiseaseConfigData;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;

class DiseaseConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var ArrayCollection<array-key, DiseaseConfig> $diseases */
        $diseases = new ArrayCollection();

        foreach (DiseaseConfigData::getAll() as $diseaseConfigDto) {
            $diseaseConfig = DiseaseConfig::fromDto($diseaseConfigDto);

            $manager->persist($diseaseConfig);
            $this->addReference($diseaseConfig->getName(), $diseaseConfig);
            $diseases->add($diseaseConfig);
        }

        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);
        $gameConfig
            ->setDiseaseConfig($diseases);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
