<?php

declare(strict_types=1);

namespace Mush\Daedalus\Factory;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Entity\Neron;
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
        $daedalusInfo = new DaedalusInfo($daedalus, new GameConfig(), self::getFrenchLocalizationConfig());
        $daedalusInfo->setNeron(new Neron());

        self::createSpacePlace($daedalus);
        self::setId($daedalus);

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

    private static function setId(Daedalus $daedalus, int $id = 1): void
    {
        $daedalusReflection = new \ReflectionClass($daedalus);
        $daedalusReflection->getProperty('id')->setValue($daedalus, $id);
    }
}
