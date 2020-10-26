<?php

namespace Mush\Game\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Mush\Game\Entity\GameConfig;

class GameConfigFixtures extends Fixture
{
    public const DEFAULT_GAME_CONFIG = 'default.game.config';

    public function load(ObjectManager $manager)
    {
        $gameConfig = new GameConfig();

        $gameConfig
            ->setName('default')
            ->setMaxPlayer(16)
            ->setNbMush(2)
            ->setCycleLength(3)
            ->setTimeZone('Europe/Paris')
            ->setLanguage('Fr-fr')
            ->setInitHealthPoint(10)
            ->setMaxHealthPoint(16)
            ->setInitMoralPoint(10)
            ->setMaxMoralPoint(16)
            ->setInitSatiety(0)
            ->setInitActionPoint(10)
            ->setMaxActionPoint(16)
            ->setInitMovementPoint(10)
            ->setMaxMovementPoint(16)
            ->setMaxItemInInventory(3)
        ;

        $manager->persist($gameConfig);

        $manager->flush();

        $this->addReference(self::DEFAULT_GAME_CONFIG, $gameConfig);
    }
}
