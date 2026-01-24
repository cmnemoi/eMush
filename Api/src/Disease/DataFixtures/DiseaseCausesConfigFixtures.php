<?php

namespace Mush\Disease\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Disease\ConfigData\DiseaseCauseConfigData;
use Mush\Disease\Entity\Config\DiseaseCauseConfig;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;

class DiseaseCausesConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var ArrayCollection<array-key, DiseaseCauseConfig> $diseaseCauses */
        $diseaseCauses = new ArrayCollection();

        foreach (DiseaseCauseConfigData::getAll() as $diseaseCauseConfigDto) {
            $diseaseCauseConfig = DiseaseCauseConfig::fromDto($diseaseCauseConfigDto);

            $manager->persist($diseaseCauseConfig);
            $this->addReference($diseaseCauseConfig->getName(), $diseaseCauseConfig);
            $diseaseCauses->add($diseaseCauseConfig);
        }

        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);
        $gameConfig
            ->setDiseaseCauseConfig($diseaseCauses);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
