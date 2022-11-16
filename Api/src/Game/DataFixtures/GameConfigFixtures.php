<?php

namespace Mush\Game\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\LanguageEnum;

class GameConfigFixtures extends Fixture
{
    public const FRENCH_DEFAULT_GAME_CONFIG = 'french.default.game.config';
    public const FRENCH_BLITZ_CYCLES_GAME_CONFIG = 'french.blitz.cycles.game.config';
    public const FRENCH_FAST_CYCLES_GAME_CONFIG = 'french.fast.cycles.game.config';
    public const FRENCH_NO_MUSH_GAME_CONFIG = 'french.no.mush.game.config';
    public const FRENCH_TRIPLE_MUSH_GAME_CONFIG = 'french.triple.mush.game.config';
    public const FRENCH_SLOW_CYCLES_GAME_CONFIG = 'french.slow.cycles.game.config';
    public const FRENCH_STARVATION_GAME_CONFIG = 'french.starvation.game.config';
    
    public const ENGLISH_DEFAULT_GAME_CONFIG = 'english.default.game.config';
    public const ENGLISH_BLITZ_CYCLES_GAME_CONFIG = 'english.blitz.cycles.game.config';
    public const ENGLISH_FAST_CYCLES_GAME_CONFIG = 'english.fast.cycles.game.config';
    public const ENGLISH_NO_MUSH_GAME_CONFIG = 'english.no.mush.game.config';
    public const ENGLISH_TRIPLE_MUSH_GAME_CONFIG = 'english.triple.mush.game.config';
    public const ENGLISH_SLOW_CYCLES_GAME_CONFIG = 'english.slow.cycles.game.config';
    public const ENGLISH_STARVATION_GAME_CONFIG = 'english.starvation.game.config';

    public const SPANISH_DEFAULT_GAME_CONFIG = 'spanish.default.game.config';
    public const SPANISH_BLITZ_CYCLES_GAME_CONFIG = 'spanish.blitz.cycles.game.config';
    public const SPANISH_FAST_CYCLES_GAME_CONFIG = 'spanish.fast.cycles.game.config';
    public const SPANISH_NO_MUSH_GAME_CONFIG = 'spanish.no.mush.game.config';
    public const SPANISH_TRIPLE_MUSH_GAME_CONFIG = 'spanish.triple.mush.game.config';
    public const SPANISH_SLOW_CYCLES_GAME_CONFIG = 'spanish.slow.cycles.game.config';
    public const SPANISH_STARVATION_GAME_CONFIG = 'spanish.starvation.game.config';

    public function load(ObjectManager $manager): void
    {
        $frenchDefaultGameConfig = new GameConfig();
        $frenchDefaultGameConfig
            ->setName(GameConfigEnum::FRENCH_DEFAULT)
            ->setNbMush(2)
            ->setCyclePerGameDay(8)
            ->setCycleLength(3 * 60)
            ->setTimeZone('Europe/Paris')
            ->setLanguage(LanguageEnum::FRENCH)
            ->setMaxNumberPrivateChannel(3)
            ->setInitHealthPoint(14)
            ->setMaxHealthPoint(14)
            ->setInitMoralPoint(7)
            ->setMaxMoralPoint(14)
            ->setInitSatiety(0)
            ->setInitActionPoint(8)
            ->setMaxActionPoint(12)
            ->setInitMovementPoint(12)
            ->setMaxMovementPoint(12)
            ->setMaxItemInInventory(3)
        ;
        $manager->persist($frenchDefaultGameConfig);

        $frenchBlitzCyclesGameConfig = clone $frenchDefaultGameConfig;
        $frenchBlitzCyclesGameConfig
            ->setName(GameConfigEnum::FRENCH_BLITZ_CYCLES)
            ->setCycleLength(1 * 60)
        ;
        $manager->persist($frenchBlitzCyclesGameConfig);

        $frenchFastCyclesGameConfig = clone $frenchDefaultGameConfig;
        $frenchFastCyclesGameConfig
            ->setName(GameConfigEnum::FRENCH_FAST_CYCLES)
            ->setCycleLength(2 * 60)
        ;
        $manager->persist($frenchFastCyclesGameConfig);

        $frenchNoMushGameConfig = clone $frenchDefaultGameConfig;
        $frenchNoMushGameConfig
            ->setName(GameConfigEnum::FRENCH_NO_MUSH)
            ->setNbMush(0)
        ;
        $manager->persist($frenchNoMushGameConfig);

        $frenchTripleMushGameConfig = clone $frenchDefaultGameConfig;
        $frenchTripleMushGameConfig
            ->setName(GameConfigEnum::FRENCH_TRIPLE_MUSH)
            ->setNbMush(3)
        ;
        $manager->persist($frenchTripleMushGameConfig);

        $frenchSlowCyclesGameConfig = clone $frenchDefaultGameConfig;
        $frenchSlowCyclesGameConfig
            ->setName(GameConfigEnum::FRENCH_SLOW_CYCLES)
            ->setCycleLength(4 * 60)
        ;
        $manager->persist($frenchFastCyclesGameConfig);

        $frenchStarvationGameConfig = clone $frenchDefaultGameConfig;
        $frenchStarvationGameConfig
            ->setName(GameConfigEnum::FRENCH_STARVATION)
            ->setInitSatiety(-24)
        ;
        $manager->persist($frenchStarvationGameConfig);

        $englishDefaultGameConfig = clone $frenchDefaultGameConfig;
        $englishDefaultGameConfig
            ->setName(GameConfigEnum::ENGLISH_DEFAULT)
            ->setTimeZone('Europe/London')
            ->setLanguage(LanguageEnum::ENGLISH)
        ;
        $manager->persist($englishDefaultGameConfig);

        $englishBlitzCyclesGameConfig = clone $englishDefaultGameConfig;
        $englishBlitzCyclesGameConfig
            ->setName(GameConfigEnum::ENGLISH_BLITZ_CYCLES)
            ->setCycleLength(1 * 60)
        ;
        $manager->persist($englishBlitzCyclesGameConfig);

        $englishFastCyclesGameConfig = clone $englishDefaultGameConfig;
        $englishFastCyclesGameConfig
            ->setName(GameConfigEnum::ENGLISH_FAST_CYCLES)
            ->setCycleLength(2 * 60)
        ;
        $manager->persist($englishFastCyclesGameConfig);

        $englishNoMushGameConfig = clone $englishDefaultGameConfig;
        $englishNoMushGameConfig
            ->setName(GameConfigEnum::ENGLISH_NO_MUSH)
            ->setNbMush(0)
        ;
        $manager->persist($englishNoMushGameConfig);

        $englishTripleMushGameConfig = clone $englishDefaultGameConfig;
        $englishTripleMushGameConfig
            ->setName(GameConfigEnum::ENGLISH_TRIPLE_MUSH)
            ->setNbMush(3)
        ;
        $manager->persist($englishTripleMushGameConfig);

        $englishSlowCyclesGameConfig = clone $englishDefaultGameConfig;
        $englishSlowCyclesGameConfig
            ->setName(GameConfigEnum::ENGLISH_SLOW_CYCLES)
            ->setCycleLength(4 * 60)
        ;
        $manager->persist($englishSlowCyclesGameConfig);

        $englishStarvationGameConfig = clone $englishDefaultGameConfig;
        $englishStarvationGameConfig
            ->setName(GameConfigEnum::ENGLISH_STARVATION)
            ->setInitSatiety(-24)
        ;
        $manager->persist($englishStarvationGameConfig);

        $spanishDefaultGameConfig = clone $frenchDefaultGameConfig;
        $spanishDefaultGameConfig
            ->setName(GameConfigEnum::SPANISH_DEFAULT)
            ->setTimeZone('Europe/Madrid')
            ->setLanguage(LanguageEnum::SPANISH)
        ;
        $manager->persist($spanishDefaultGameConfig);

        $spanishBlitzCyclesGameConfig = clone $spanishDefaultGameConfig;
        $spanishBlitzCyclesGameConfig
            ->setName(GameConfigEnum::SPANISH_BLITZ_CYCLES)
            ->setCycleLength(1 * 60)
        ;
        $manager->persist($spanishBlitzCyclesGameConfig);

        $spanishFastCyclesGameConfig = clone $spanishDefaultGameConfig;
        $spanishFastCyclesGameConfig
            ->setName(GameConfigEnum::SPANISH_FAST_CYCLES)
            ->setCycleLength(2 * 60)
        ;
        $manager->persist($spanishFastCyclesGameConfig);

        $spanishNoMushGameConfig = clone $spanishDefaultGameConfig;
        $spanishNoMushGameConfig
            ->setName(GameConfigEnum::SPANISH_NO_MUSH)
            ->setNbMush(0)
        ;
        $manager->persist($spanishNoMushGameConfig);

        $spanishTripleMushGameConfig = clone $spanishDefaultGameConfig;
        $spanishTripleMushGameConfig
            ->setName(GameConfigEnum::SPANISH_TRIPLE_MUSH)
            ->setNbMush(3)
        ;
        $manager->persist($spanishTripleMushGameConfig);

        $spanishSlowCyclesGameConfig = clone $spanishDefaultGameConfig;
        $spanishSlowCyclesGameConfig
            ->setName(GameConfigEnum::SPANISH_SLOW_CYCLES)
            ->setCycleLength(4 * 60)
        ;
        $manager->persist($spanishSlowCyclesGameConfig);

        $spanishStarvationGameConfig = clone $spanishDefaultGameConfig;
        $spanishStarvationGameConfig
            ->setName(GameConfigEnum::SPANISH_STARVATION)
            ->setInitSatiety(-24)
        ;
        $manager->persist($spanishStarvationGameConfig);

        $manager->flush();

        $this->addReference(self::FRENCH_DEFAULT_GAME_CONFIG, $frenchDefaultGameConfig);
        $this->addReference(self::FRENCH_BLITZ_CYCLES_GAME_CONFIG, $frenchBlitzCyclesGameConfig);
        $this->addReference(self::FRENCH_FAST_CYCLES_GAME_CONFIG, $frenchFastCyclesGameConfig);
        $this->addReference(self::FRENCH_NO_MUSH_GAME_CONFIG, $frenchNoMushGameConfig);
        $this->addReference(self::FRENCH_TRIPLE_MUSH_GAME_CONFIG, $frenchTripleMushGameConfig);
        $this->addReference(self::FRENCH_SLOW_CYCLES_GAME_CONFIG, $frenchSlowCyclesGameConfig);
        $this->addReference(self::FRENCH_STARVATION_GAME_CONFIG, $frenchStarvationGameConfig);

        $this->addReference(self::ENGLISH_DEFAULT_GAME_CONFIG, $englishDefaultGameConfig);
        $this->addReference(self::ENGLISH_BLITZ_CYCLES_GAME_CONFIG, $englishBlitzCyclesGameConfig);
        $this->addReference(self::ENGLISH_FAST_CYCLES_GAME_CONFIG, $englishFastCyclesGameConfig);
        $this->addReference(self::ENGLISH_NO_MUSH_GAME_CONFIG, $englishNoMushGameConfig);
        $this->addReference(self::ENGLISH_TRIPLE_MUSH_GAME_CONFIG, $englishTripleMushGameConfig);
        $this->addReference(self::ENGLISH_SLOW_CYCLES_GAME_CONFIG, $englishSlowCyclesGameConfig);
        $this->addReference(self::ENGLISH_STARVATION_GAME_CONFIG, $englishStarvationGameConfig);

        $this->addReference(self::SPANISH_DEFAULT_GAME_CONFIG, $spanishDefaultGameConfig);
        $this->addReference(self::SPANISH_BLITZ_CYCLES_GAME_CONFIG, $spanishBlitzCyclesGameConfig);
        $this->addReference(self::SPANISH_FAST_CYCLES_GAME_CONFIG, $spanishFastCyclesGameConfig);
        $this->addReference(self::SPANISH_NO_MUSH_GAME_CONFIG, $spanishNoMushGameConfig);
        $this->addReference(self::SPANISH_TRIPLE_MUSH_GAME_CONFIG, $spanishTripleMushGameConfig);
        $this->addReference(self::SPANISH_SLOW_CYCLES_GAME_CONFIG, $spanishSlowCyclesGameConfig);
        $this->addReference(self::SPANISH_STARVATION_GAME_CONFIG, $spanishStarvationGameConfig);
    }
}
