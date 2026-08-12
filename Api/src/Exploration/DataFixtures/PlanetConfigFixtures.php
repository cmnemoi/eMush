<?php

declare(strict_types=1);

namespace Mush\Exploration\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Exploration\ConfigData\PlanetConfigData;
use Mush\Exploration\Entity\PlanetConfig;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;

/** @codeCoverageIgnore */
final class PlanetConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        /** @var ArrayCollection<int, PlanetConfig> $planetConfigs */
        $planetConfigs = new ArrayCollection();

        foreach (PlanetConfigData::$dataArray as $data) {
            $planetConfig = PlanetConfig::fromConfigData($data);
            $manager->persist($planetConfig);
            $planetConfigs->add($planetConfig);
        }

        $gameConfig->setPlanetConfigs($planetConfigs);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
            PlanetSectorEventConfigFixtures::class,
        ];
    }
}
