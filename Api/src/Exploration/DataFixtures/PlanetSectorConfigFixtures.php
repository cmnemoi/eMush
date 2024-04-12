<?php

declare(strict_types=1);

namespace Mush\Exploration\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Exploration\ConfigData\PlanetSectorConfigData;
use Mush\Exploration\Entity\PlanetSectorConfig;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;

/** @codeCoverageIgnore */
final class PlanetSectorConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        /** @var ArrayCollection<int, PlanetSectorConfig> $planetSectorConfigs */
        $planetSectorConfigs = new ArrayCollection();

        foreach (PlanetSectorConfigData::$dataArray as $data) {
            $planetSectorConfig = new PlanetSectorConfig();
            $planetSectorConfig
                ->setName($data['name'])
                ->setSectorName($data['sectorName'])
                ->setWeightAtPlanetGeneration($data['weightAtPlanetGeneration'])
                ->setWeightAtPlanetAnalysis($data['weightAtPlanetAnalysis'])
                ->setWeightAtPlanetExploration($data['weightAtPlanetExploration'])
                ->setMaxPerPlanet($data['maxPerPlanet'])
                ->setExplorationEvents($data['explorationEvents']);
            $manager->persist($planetSectorConfig);
            $planetSectorConfigs->add($planetSectorConfig);
        }

        $gameConfig->setPlanetSectorConfigs($planetSectorConfigs);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
