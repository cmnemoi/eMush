<?php

namespace Mush\Game\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\LanguageEnum;

class GameConfigFixtures extends Fixture
{
    public const DEFAULT_GAME_CONFIG = 'default.game.config';
    public const FRENCH_LOCALIZATION_CONFIG = 'french.localization.config';

    public function load(ObjectManager $manager): void
    {
        $localizationConfig = new LocalizationConfig();
        $localizationConfig
            ->setTimeZone('Europe/Paris')
            ->setLanguage(LanguageEnum::FRENCH)
        ;

        $manager->persist($localizationConfig);

        $gameConfig = new GameConfig();

        $gameConfig
            ->setName('default')
        ;

        $manager->persist($gameConfig);

        $manager->flush();

        $this->addReference(self::DEFAULT_GAME_CONFIG, $gameConfig);
        $this->addReference(self::FRENCH_LOCALIZATION_CONFIG, $localizationConfig);
    }
}
