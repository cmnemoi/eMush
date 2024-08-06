<?php

declare(strict_types=1);

namespace Mush\Daedalus\Factory;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Entity\Neron;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Factory\GameEquipmentFactory;
use Mush\Game\Entity\DifficultyConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\LanguageEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\Skill\ConfigData\SkillConfigData;
use Mush\Skill\Entity\SkillConfig;
use Symfony\Component\Uid\Uuid;

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
        $daedalusInfo->setName(Uuid::v4()->toRfc4122());

        self::createSpacePlace($daedalus);
        self::createLaboratoryPlace($daedalus);
        self::createMycoscanEquipment($daedalus);
        self::setupId($daedalus);

        $gameConfig->setDifficultyConfig(self::getDifficultyConfig());
        $gameConfig->setMushSkillConfigs(self::getMushSkillConfigs());

        return $daedalus;
    }

    private static function createSpacePlace(Daedalus $daedalus): void
    {
        $space = new Place();
        $space
            ->setName(RoomEnum::SPACE)
            ->setType(PlaceTypeEnum::SPACE)
            ->setDaedalus($daedalus);

        (new \ReflectionProperty($space, 'id'))->setValue($space, (int) hash('crc32b', serialize($space)));
    }

    private static function createLaboratoryPlace(Daedalus $daedalus): void
    {
        $laboratory = new Place();
        $laboratory
            ->setName(RoomEnum::LABORATORY)
            ->setType(PlaceTypeEnum::ROOM)
            ->setDaedalus($daedalus);

        (new \ReflectionProperty($laboratory, 'id'))->setValue($laboratory, (int) hash('crc32b', serialize($laboratory)));
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

    private static function setupId(Daedalus $daedalus): void
    {
        (new \ReflectionProperty($daedalus, 'id'))->setValue($daedalus, random_int(1, PHP_INT_MAX));
    }

    private static function getMushSkillConfigs(): ArrayCollection
    {
        /** @var ArrayCollection<array-key, SkillConfig> $mushSkillConfigs */
        $mushSkillConfigs = new ArrayCollection();
        foreach (SkillConfigData::getAll() as $skillConfigDto) {
            $mushSkillConfigs->add(SkillConfig::createFromDto($skillConfigDto));
        }

        return $mushSkillConfigs;
    }
}
