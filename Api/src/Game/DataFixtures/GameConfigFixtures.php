<?php

namespace Mush\Game\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameConfigEnum;

class GameConfigFixtures extends Fixture
{
    public const DEFAULT_GAME_CONFIG = 'default.game.config';

    public function load(ObjectManager $manager): void
    {
        $gameConfig = new GameConfig();

        $gameConfig
            ->setName(GameConfigEnum::DEFAULT);

        $manager->persist($gameConfig);

        $manager->flush();

        $this->addReference(self::DEFAULT_GAME_CONFIG, $gameConfig);
    }
}
