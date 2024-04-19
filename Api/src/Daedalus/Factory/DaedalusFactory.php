<?php

declare(strict_types=1);

namespace Mush\Daedalus\Factory;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\LanguageEnum;

final class DaedalusFactory
{
    public static function createDaedalus(): Daedalus
    {
        $daedalus = new Daedalus();
        new DaedalusInfo($daedalus, new GameConfig(), self::getFrenchLocalizationConfig());

        return $daedalus;
    }

    private static function getFrenchLocalizationConfig(): LocalizationConfig
    {
        $localizationConfig = new LocalizationConfig();
        $localizationConfig
            ->setName(LanguageEnum::FRENCH)
            ->setLanguage(LanguageEnum::FRENCH)
            ->setTimeZone('Europe/Paris');

        return $localizationConfig;
    }
}
