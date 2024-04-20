<?php

declare(strict_types=1);

namespace Mush\Daedalus\Factory;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\LanguageEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Place\Enum\RoomEnum;

final class DaedalusFactory
{
    public static function createDaedalus(): Daedalus
    {
        $daedalus = new Daedalus();
        $daedalus->setDaedalusVariables(new DaedalusConfig());
        new DaedalusInfo($daedalus, new GameConfig(), self::getFrenchLocalizationConfig());

        self::createSpacePlace($daedalus);
        self::setId($daedalus, 1);

        return $daedalus;
    }

    private static function createSpacePlace(Daedalus $daedalus): void
    {
        $space = new Place();
        $space
            ->setName(RoomEnum::SPACE)
            ->setType(PlaceTypeEnum::SPACE)
            ->setDaedalus($daedalus);
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

    private static function setId(Daedalus $daedalus, int $id): void
    {
        $daedalusReflection = new \ReflectionClass($daedalus);
        $daedalusReflection->getProperty('id')->setValue($daedalus, $id);
    }
}
