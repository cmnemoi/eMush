<?php

declare(strict_types=1);

namespace Mush\Achievement\ConfigData;

use Mush\Achievement\Dto\StatisticConfigDto;
use Mush\Achievement\Enum\StatisticEnum;

/** @codeCoverageIgnore */
abstract class StatisticConfigData
{
    /** @return StatisticConfigDto[] */
    public static function getAll(): array
    {
        return [
            new StatisticConfigDto(StatisticEnum::CAT_CUDDLED),
            new StatisticConfigDto(StatisticEnum::COFFEE_TAKEN),
            new StatisticConfigDto(StatisticEnum::COOKED_TAKEN),
            new StatisticConfigDto(StatisticEnum::DOOR_REPAIRED),
            new StatisticConfigDto(StatisticEnum::EXPLO_FEED),
            new StatisticConfigDto(StatisticEnum::EXPLORER),
            new StatisticConfigDto(StatisticEnum::BACK_TO_ROOT, isRare: true),
            new StatisticConfigDto(StatisticEnum::CAMERA_INSTALLED),
            new StatisticConfigDto(StatisticEnum::EXTINGUISH_FIRE),
            new StatisticConfigDto(StatisticEnum::GAGGED),
            new StatisticConfigDto(StatisticEnum::GIVE_MISSION),
            new StatisticConfigDto(StatisticEnum::DAILY_ORDER),
            new StatisticConfigDto(StatisticEnum::GAME_WITHOUT_SLEEP),
            new StatisticConfigDto(StatisticEnum::PLANET_SCANNED),
            new StatisticConfigDto(StatisticEnum::SIGNAL_EQUIP),
            new StatisticConfigDto(StatisticEnum::SIGNAL_FIRE),
            new StatisticConfigDto(StatisticEnum::SUCCEEDED_INSPECTION),
            new StatisticConfigDto(StatisticEnum::NEW_PLANTS),
            new StatisticConfigDto(StatisticEnum::ANDIE),
            new StatisticConfigDto(StatisticEnum::CHUN),
            new StatisticConfigDto(StatisticEnum::KUAN_TI),
            new StatisticConfigDto(StatisticEnum::CHAO),
            new StatisticConfigDto(StatisticEnum::ELEESHA),
            new StatisticConfigDto(StatisticEnum::FINOLA),
            new StatisticConfigDto(StatisticEnum::FRIEDA),
            new StatisticConfigDto(StatisticEnum::HUA),
            new StatisticConfigDto(StatisticEnum::JANICE),
            new StatisticConfigDto(StatisticEnum::JIN_SU),
            new StatisticConfigDto(StatisticEnum::CONTRIBUTIONS, isRare: true),
            new StatisticConfigDto(StatisticEnum::IAN),
            new StatisticConfigDto(StatisticEnum::STEPHEN),
            new StatisticConfigDto(StatisticEnum::DEREK),
            new StatisticConfigDto(StatisticEnum::GIOELE),
            new StatisticConfigDto(StatisticEnum::PAOLA),
            new StatisticConfigDto(StatisticEnum::RALUCA),
            new StatisticConfigDto(StatisticEnum::ROLAND),
            new StatisticConfigDto(StatisticEnum::TERRENCE),
            new StatisticConfigDto(StatisticEnum::EDEN, isRare: true),
            new StatisticConfigDto(StatisticEnum::MUSH_CYCLES),
            new StatisticConfigDto(StatisticEnum::EDEN_CONTAMINATED, isRare: true),
            new StatisticConfigDto(StatisticEnum::HUNTER_DOWN),
            new StatisticConfigDto(StatisticEnum::POLITICIAN, isRare: true),
            new StatisticConfigDto(StatisticEnum::LIKES),
            new StatisticConfigDto(StatisticEnum::SURGEON),
            new StatisticConfigDto(StatisticEnum::BUTCHER),
            new StatisticConfigDto(StatisticEnum::COMMUNICATION_EXPERT, isRare: true),
            new StatisticConfigDto(StatisticEnum::DAY_5_REACHED),
            new StatisticConfigDto(StatisticEnum::DAY_10_REACHED),
            new StatisticConfigDto(StatisticEnum::DAY_15_REACHED, isRare: true),
            new StatisticConfigDto(StatisticEnum::DAY_20_REACHED, isRare: true),
            new StatisticConfigDto(StatisticEnum::DAY_30_REACHED, isRare: true),
            new StatisticConfigDto(StatisticEnum::MUSH_GENOME),
            new StatisticConfigDto(StatisticEnum::REBELS),
            new StatisticConfigDto(StatisticEnum::PILGRED_IS_BACK, isRare: true),
            new StatisticConfigDto(StatisticEnum::DAY_MAX),
            new StatisticConfigDto(StatisticEnum::DRUGS_TAKEN),
        ];
    }

    public static function getByName(StatisticEnum $name): StatisticConfigDto
    {
        if ($name === StatisticEnum::NULL) {
            return new StatisticConfigDto(StatisticEnum::NULL);
        }

        $statistics = array_filter(self::getAll(), static fn (StatisticConfigDto $dto) => $dto->name === $name);
        $statistic = current($statistics);

        if (!$statistic) {
            throw new \Exception("Statistic config {$name->value} not found!");
        }

        return $statistic;
    }
}
