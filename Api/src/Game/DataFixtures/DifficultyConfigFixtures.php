<?php

namespace Mush\Game\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Game\ConfigData\DifficultyConfigData;
use Mush\Game\Entity\DifficultyConfig;
use Mush\Game\Entity\GameConfig;

class DifficultyConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public const string DEFAULT_DIFFICULTY_CONFIG = 'default_difficulty_config';

    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        $difficultyConfig = DifficultyConfig::fromDto(DifficultyConfigData::getByName('default'));

        $manager->persist($difficultyConfig);

        $gameConfig->setDifficultyConfig($difficultyConfig);
        $manager->persist($gameConfig);

        $manager->flush();

        $this->addReference(self::DEFAULT_DIFFICULTY_CONFIG, $difficultyConfig);
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
