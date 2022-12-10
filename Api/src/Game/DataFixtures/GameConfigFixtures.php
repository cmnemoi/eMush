<?php

namespace Mush\Game\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameConfigEnum;

class GameConfigFixtures extends Fixture
{
    public const ALPHA_GAME_CONFIG = 'alpha.game.config';
    public const DEFAULT_GAME_CONFIG = 'default.game.config';

    public function load(ObjectManager $manager): void
    {
        $alphaGameConfig = new GameConfig();
        $alphaGameConfig
            ->setName(GameConfigEnum::ALPHA)
        ;
        $manager->persist($alphaGameConfig);
        
        $defaultGameConfig = new GameConfig();
        $defaultGameConfig
            ->setName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($defaultGameConfig);

        $manager->flush();

        $this->addReference(self::ALPHA_GAME_CONFIG, $alphaGameConfig);
        $this->addReference(self::DEFAULT_GAME_CONFIG, $defaultGameConfig);
    }
}
