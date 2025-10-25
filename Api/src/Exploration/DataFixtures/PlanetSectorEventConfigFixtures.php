<?php

declare(strict_types=1);

namespace Mush\Exploration\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Exploration\ConfigData\PlanetSectorEventConfigData;
use Mush\Exploration\Entity\PlanetSectorEventConfig;
use Mush\Exploration\Enum\PlanetSectorEventTagEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;

/** @codeCoverageIgnore */
final class PlanetSectorEventConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        foreach (PlanetSectorEventConfigData::getAll() as $data) {
            $planetSectorEventConfig = PlanetSectorEventConfig::fromDto($data);
            $this->addReference($planetSectorEventConfig->getName(), $planetSectorEventConfig);
            $manager->persist($planetSectorEventConfig);
        }

        $planetSectorEventConfig = new PlanetSectorEventConfig(name: 'fight_1', eventName: 'fight');
        $planetSectorEventConfig
            ->setOutputQuantity([])
            ->setOutputTable([1 => 1])
            ->setTag(PlanetSectorEventTagEnum::NEGATIVE);

        $this->addReference($planetSectorEventConfig->getName(), $planetSectorEventConfig);
        $manager->persist($planetSectorEventConfig);

        $planetSectorEventConfig = new PlanetSectorEventConfig(name: 'fight_2', eventName: 'fight');
        $planetSectorEventConfig
            ->setOutputQuantity([])
            ->setOutputTable([2 => 1])
            ->setTag(PlanetSectorEventTagEnum::NEGATIVE);
        $this->addReference($planetSectorEventConfig->getName(), $planetSectorEventConfig);
        $manager->persist($planetSectorEventConfig);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
