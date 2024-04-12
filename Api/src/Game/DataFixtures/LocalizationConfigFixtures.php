<?php

namespace Mush\Game\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\LanguageEnum;

class LocalizationConfigFixtures extends Fixture
{
    public const FRENCH_LOCALIZATION_CONFIG = 'french.localization.config';
    public const ENGLISH_LOCALIZATION_CONFIG = 'english.localization.config';

    public function load(ObjectManager $manager): void
    {
        $frenchLocalizationConfig = new LocalizationConfig();
        $frenchLocalizationConfig
            ->setName(LanguageEnum::FRENCH)
            ->setTimeZone('Europe/Paris')
            ->setLanguage(LanguageEnum::FRENCH);

        $manager->persist($frenchLocalizationConfig);

        $englishLocalizationConfig = new LocalizationConfig();
        $englishLocalizationConfig
            ->setName(LanguageEnum::ENGLISH)
            ->setTimeZone('UTC')
            ->setLanguage(LanguageEnum::ENGLISH);

        $manager->persist($englishLocalizationConfig);

        $manager->flush();

        $this->addReference(self::FRENCH_LOCALIZATION_CONFIG, $frenchLocalizationConfig);
        $this->addReference(self::ENGLISH_LOCALIZATION_CONFIG, $englishLocalizationConfig);
    }
}
