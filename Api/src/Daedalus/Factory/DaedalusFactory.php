<?php

declare(strict_types=1);

namespace Mush\Daedalus\Factory;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Entity\Neron;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Factory\GameEquipmentFactory;
use Mush\Game\Entity\DifficultyConfig;
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

        $gameConfig = new GameConfig();
        $gameConfig->setDaedalusConfig(new DaedalusConfig());


        $daedalus->setDaedalusVariables($gameConfig->getDaedalusConfig());
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, self::getFrenchLocalizationConfig());
        $daedalusInfo->setNeron(new Neron());

        self::createSpacePlace($daedalus);
        self::createLaboratoryPlace($daedalus);
        self::createMycoscanEquipment($daedalus);
        self::setId($daedalus);

        $gameConfig->setDifficultyConfig(self::getDifficultyConfig());

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

    private static function createLaboratoryPlace(Daedalus $daedalus): void
    {
        $laboratory = new Place();
        $laboratory
            ->setName(RoomEnum::LABORATORY)
            ->setType(PlaceTypeEnum::ROOM)
            ->setDaedalus($daedalus);
    }

    private static function createMycoscanEquipment(Daedalus $daedalus): void
    {
        GameEquipmentFactory::createEquipmentByNameForHolder(
            name: EquipmentEnum::MYCOSCAN, 
            holder: $daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY)
        );
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

    private static function getDifficultyConfig(): DifficultyConfig
    {
        $difficultyConfig = new DifficultyConfig();
        $difficultyConfig->setEquipmentBreakRateDistribution([EquipmentEnum::MYCOSCAN => 1]);

        return $difficultyConfig;
    }

    private static function setId(Daedalus $daedalus, int $id = 1): void
    {
        $daedalusReflection = new \ReflectionClass($daedalus);
        $daedalusReflection->getProperty('id')->setValue($daedalus, $id);
    }
}
