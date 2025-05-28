<?php

namespace Mush\Triumph\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Triumph\ConfigData\TriumphConfigData;
use Mush\Triumph\Entity\TriumphConfig;

final class TriumphConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        foreach (TriumphConfigData::getAll() as $triumphConfigDto) {
            $triumphConfig = TriumphConfig::fromDto($triumphConfigDto);
            $manager->persist($triumphConfig);
            $this->addReference('triumph_config_' . $triumphConfigDto->name->toString(), $triumphConfig);

            $gameConfig->addTriumphConfig($triumphConfig);
        }

        $manager->persist($gameConfig);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [GameConfigFixtures::class];
    }
}
