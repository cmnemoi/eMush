<?php

declare(strict_types=1);

namespace Mush\Daedalus\Factory;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Communications\ConfigData\TradeConfigData;
use Mush\Communications\Entity\TradeConfig;
use Mush\Daedalus\ConfigData\DaedalusConfigData;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Entity\Neron;
use Mush\Disease\ConfigData\DiseaseCauseConfigData;
use Mush\Disease\ConfigData\DiseaseConfigData;
use Mush\Disease\Entity\Config\DiseaseCauseConfig;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Equipment\ConfigData\EquipmentConfigData;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Factory\GameEquipmentFactory;
use Mush\Game\ConfigData\DifficultyConfigData;
use Mush\Game\Entity\DifficultyConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\LanguageEnum;
use Mush\Hunter\ConfigData\HunterConfigData;
use Mush\Hunter\Entity\HunterConfig;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\ConfigData\CharacterConfigData;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Skill\ConfigData\SkillConfigData;
use Mush\Skill\Entity\SkillConfig;
use Symfony\Component\Uid\Uuid;

final class DaedalusFactory
{
    public static function createDaedalus(): Daedalus
    {
        $daedalus = new Daedalus();
        $daedalus->setCreatedAt(new \DateTime());

        $gameConfig = new GameConfig();
        $gameConfig->setDaedalusConfig(DaedalusConfig::fromConfigData(DaedalusConfigData::getByName('default')));

        $daedalus->setDaedalusVariables($gameConfig->getDaedalusConfig());
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, self::getFrenchLocalizationConfig());
        $daedalusInfo->setNeron(new Neron());
        $daedalusInfo->setName(Uuid::v4()->toRfc4122());

        self::createSpacePlace($daedalus);
        self::createLaboratoryPlaceDifferentFromSpacePlace($daedalus);
        self::createMycoscanEquipment($daedalus);
        self::setupId($daedalus);

        $gameConfig->setDifficultyConfig(self::getDifficultyConfig());
        $gameConfig->setSkillConfigs(self::getMushSkillConfigs());
        $gameConfig->setHunterConfigs(self::getHunterConfigs());
        $gameConfig->setEquipmentsConfig(self::getEquipmentConfigs());
        $gameConfig->setTradeConfigs(self::getTradeConfigs());
        $gameConfig->setDiseaseCauseConfig(self::getDiseaseCauseConfigs());
        $gameConfig->setDiseaseConfig(self::getDiseaseConfigs());
        $gameConfig->setCharactersConfig(self::getCharacterConfigs());

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

    private static function createLaboratoryPlaceDifferentFromSpacePlace(Daedalus $daedalus): void
    {
        self::createLaboratoryPlace($daedalus);
        $space = $daedalus->getPlaceByNameOrThrow(RoomEnum::SPACE);
        $laboratory = $daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY);

        if ($space->getId() === $laboratory->getId()) {
            (new \ReflectionProperty($laboratory, 'id'))->setValue($laboratory, $laboratory->getId() + 1);
        }
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
        $difficultyConfig = DifficultyConfig::fromDto(DifficultyConfigData::getByName('default'));
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

    private static function getHunterConfigs(): ArrayCollection
    {
        /** @var ArrayCollection<array-key, HunterConfig> $hunterConfigs */
        $hunterConfigs = new ArrayCollection();
        foreach (HunterConfigData::$dataArray as $hunterConfigDto) {
            $hunterConfigs->add(HunterConfig::fromConfigData($hunterConfigDto));
        }

        return $hunterConfigs;
    }

    private static function getEquipmentConfigs(): ArrayCollection
    {
        /** @var ArrayCollection<array-key, EquipmentConfig> $equipmentConfigs */
        $equipmentConfigs = new ArrayCollection();
        foreach (EquipmentConfigData::$dataArray as $equipmentConfigData) {
            $equipmentConfigs->add(EquipmentConfig::fromConfigData($equipmentConfigData));
        }

        return $equipmentConfigs;
    }

    private static function getTradeConfigs(): ArrayCollection
    {
        /** @var ArrayCollection<array-key, TradeConfig> $tradeConfigs */
        $tradeConfigs = new ArrayCollection();
        foreach (TradeConfigData::getAll() as $tradeConfigDto) {
            $tradeConfigs->add(TradeConfig::fromDto($tradeConfigDto));
        }

        return $tradeConfigs;
    }

    private static function getDiseaseCauseConfigs(): ArrayCollection
    {
        /** @var ArrayCollection<array-key, DiseaseCauseConfig> $diseaseCauseConfigs */
        $diseaseCauseConfigs = new ArrayCollection();
        foreach (DiseaseCauseConfigData::$dataArray as $diseaseCauseConfigData) {
            $diseaseCauseConfigs->add(DiseaseCauseConfig::fromConfigData($diseaseCauseConfigData));
        }

        return $diseaseCauseConfigs;
    }

    private static function getDiseaseConfigs(): ArrayCollection
    {
        /** @var ArrayCollection<array-key, DiseaseConfig> $diseaseConfigs */
        $diseaseConfigs = new ArrayCollection();
        foreach (DiseaseConfigData::$dataArray as $diseaseConfigData) {
            $diseaseConfigs->add(DiseaseConfig::fromConfigData($diseaseConfigData));
        }

        return $diseaseConfigs;
    }

    private static function getCharacterConfigs(): ArrayCollection
    {
        /** @var ArrayCollection<array-key, CharacterConfig> $characterConfigs */
        $characterConfigs = new ArrayCollection();
        foreach (CharacterConfigData::$dataArray as $characterConfigData) {
            $characterConfigs->add(CharacterConfig::fromConfigData($characterConfigData));
        }

        return $characterConfigs;
    }
}
