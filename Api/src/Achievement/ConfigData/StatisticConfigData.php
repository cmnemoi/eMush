<?php

declare(strict_types=1);

namespace Mush\Achievement\ConfigData;

use Mush\Achievement\Dto\StatisticConfigDto;
use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Enum\StatisticStrategyEnum;

/** @codeCoverageIgnore */
abstract class StatisticConfigData
{
    /** @return StatisticConfigDto[] */
    public static function getAll(): array
    {
        return [
            new StatisticConfigDto(StatisticEnum::CAT_CUDDLED, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::COFFEE_TAKEN, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::COOKED_TAKEN, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::DOOR_REPAIRED, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::EXPLO_FEED, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::EXPLORER, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::BACK_TO_ROOT, StatisticStrategyEnum::INCREMENT, isRare: true),
            new StatisticConfigDto(StatisticEnum::CAMERA_INSTALLED, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::EXTINGUISH_FIRE, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::GAGGED, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::GIVE_MISSION, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::DAILY_ORDER, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::GAME_WITHOUT_SLEEP, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::PLANET_SCANNED, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::SIGNAL_EQUIP, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::SIGNAL_FIRE, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::SUCCEEDED_INSPECTION, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::HAS_MUSHED, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::MUSHED, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::NEW_PLANTS, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::ANDIE, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::CHUN, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::KUAN_TI, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::CHAO, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::ELEESHA, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::FINOLA, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::FRIEDA, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::HUA, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::JANICE, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::JIN_SU, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::CONTRIBUTIONS, StatisticStrategyEnum::MAX, isRare: true),
            new StatisticConfigDto(StatisticEnum::IAN, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::STEPHEN, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::DEREK, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::GIOELE, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::PAOLA, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::RALUCA, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::ROLAND, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::TERRENCE, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::EDEN, StatisticStrategyEnum::INCREMENT, isRare: true),
            new StatisticConfigDto(StatisticEnum::MUSH_CYCLES, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::EDEN_CONTAMINATED, StatisticStrategyEnum::INCREMENT, isRare: true),
            new StatisticConfigDto(StatisticEnum::MAGE_BOOK_LEARNED, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::NATAMIST, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::HUNTER_DOWN, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::POLITICIAN, StatisticStrategyEnum::INCREMENT, isRare: true),
            new StatisticConfigDto(StatisticEnum::LIKES, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::SURGEON, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::BUTCHER, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::COMMUNICATION_EXPERT, StatisticStrategyEnum::INCREMENT, isRare: true),
            new StatisticConfigDto(StatisticEnum::DAY_5_REACHED, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::DAY_10_REACHED, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::DAY_15_REACHED, StatisticStrategyEnum::INCREMENT, isRare: true),
            new StatisticConfigDto(StatisticEnum::DAY_20_REACHED, StatisticStrategyEnum::INCREMENT, isRare: true),
            new StatisticConfigDto(StatisticEnum::DAY_30_REACHED, StatisticStrategyEnum::INCREMENT, isRare: true),
            new StatisticConfigDto(StatisticEnum::MUSH_GENOME, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::REBELS, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::PILGRED_IS_BACK, StatisticStrategyEnum::INCREMENT, isRare: true),
            new StatisticConfigDto(StatisticEnum::COFFEE_MAN, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::RATION_TAKEN, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::DAY_MAX, StatisticStrategyEnum::MAX),
            new StatisticConfigDto(StatisticEnum::PROJECT_COMPLETE, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::RESEARCH_COMPLETE, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::DRUGS_TAKEN, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::KIVANC_CONTACTED, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::NILS_CONTACTED, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::ARTEFACT_SPECIALIST, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::TEAM_ALL_REBELS, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::TEAM_REBELS, StatisticStrategyEnum::MAX),
            new StatisticConfigDto(StatisticEnum::PLASMA_SHIELD, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::GRENADIER, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::FROZEN_TAKEN, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::KIND_PERSON, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::DISEASE_CONTRACTED, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::SHRINKER, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::PHYSICIAN, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::MANKAROG_DOWN, StatisticStrategyEnum::INCREMENT, isRare: true),
            new StatisticConfigDto(StatisticEnum::MUSH_KILLED, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::TEAM_MUSH_KILLED, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::ARTEFACT_COLL, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::PROJECT_TEAM, StatisticStrategyEnum::MAX),
            new StatisticConfigDto(StatisticEnum::RESEARCH_TEAM, StatisticStrategyEnum::MAX),
            new StatisticConfigDto(StatisticEnum::TAGS_COMPLETE, StatisticStrategyEnum::MAX),
            new StatisticConfigDto(StatisticEnum::LAST_MEMBER, StatisticStrategyEnum::INCREMENT, isRare: true),
            new StatisticConfigDto(StatisticEnum::COMMANDER_SHOULD_GO_LAST, StatisticStrategyEnum::INCREMENT, isRare: true),
            new StatisticConfigDto(StatisticEnum::TRIUMPH, StatisticStrategyEnum::INCREMENT),
            new StatisticConfigDto(StatisticEnum::VENERIAN_DISEASE, StatisticStrategyEnum::INCREMENT),
        ];
    }

    public static function getByName(StatisticEnum $name): StatisticConfigDto
    {
        if ($name === StatisticEnum::NULL) {
            return new StatisticConfigDto(StatisticEnum::NULL, StatisticStrategyEnum::NULL);
        }

        $statistics = array_filter(self::getAll(), static fn (StatisticConfigDto $dto) => $dto->name === $name);
        $statistic = current($statistics);

        if (!$statistic) {
            throw new \Exception("Statistic config {$name->value} not found!");
        }

        return $statistic;
    }
}
