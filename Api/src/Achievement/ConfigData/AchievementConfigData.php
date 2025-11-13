<?php

declare(strict_types=1);

namespace Mush\Achievement\ConfigData;

use Mush\Achievement\Dto\AchievementConfigDto;
use Mush\Achievement\Enum\AchievementEnum;

/** @codeCoverageIgnore */
abstract class AchievementConfigData
{
    /** @return AchievementConfigDto[] */
    public static function getAll(): array
    {
        return [
            new AchievementConfigDto(
                name: AchievementEnum::GAGGED_1,
                points: 0,
                threshold: 1,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::CAMERA_INSTALLED_1,
                points: 0,
                threshold: 1,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::CAT_CUDDLED_1,
                points: 1,
                threshold: 1,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::COFFEE_TAKEN_1,
                points: 0,
                threshold: 1,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::COOKED_TAKEN_1,
                points: 1,
                threshold: 1,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::DOOR_REPAIRED_1,
                points: 1,
                threshold: 1,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::GIVE_MISSION_1,
                points: 1,
                threshold: 1,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::GIVE_MISSION_10,
                points: 1,
                threshold: 10,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::GIVE_MISSION_50,
                points: 1,
                threshold: 50,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::GIVE_MISSION_100,
                points: 1,
                threshold: 100,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::GIVE_MISSION_500,
                points: 1,
                threshold: 500,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::DAILY_ORDER_1,
                points: 1,
                threshold: 1,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::DAILY_ORDER_10,
                points: 1,
                threshold: 10,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::DAILY_ORDER_20,
                points: 1,
                threshold: 20,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::EXPLO_FEED_1,
                points: 1,
                threshold: 1,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::EXPLO_FEED_50,
                points: 5,
                threshold: 50,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::EXPLO_FEED_200,
                points: 10,
                threshold: 200,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::EXPLO_FEED_500,
                points: 20,
                threshold: 500,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::EXPLO_FEED_1000,
                points: 40,
                threshold: 1_000,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::EXPLORER_1,
                points: 1,
                threshold: 1,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::EXPLORER_50,
                points: 5,
                threshold: 50,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::EXPLORER_200,
                points: 10,
                threshold: 200,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::EXPLORER_1000,
                points: 5,
                threshold: 1_000,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::BACK_TO_ROOT_1,
                points: 1,
                threshold: 1,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::GAME_WITHOUT_SLEEP_1,
                points: 5,
                threshold: 1,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::GAME_WITHOUT_SLEEP_20,
                points: 5,
                threshold: 20,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::GAME_WITHOUT_SLEEP_100,
                points: 5,
                threshold: 100,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::PLANET_SCANNED_1,
                points: 1,
                threshold: 1,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::SIGNAL_EQUIP_1,
                points: 0,
                threshold: 1,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::SIGNAL_EQUIP_20,
                points: 0,
                threshold: 20,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::SIGNAL_EQUIP_50,
                points: 0,
                threshold: 50,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::SIGNAL_EQUIP_200,
                points: 0,
                threshold: 200,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::SIGNAL_EQUIP_1000,
                points: 0,
                threshold: 1_000,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::SUCCEEDED_INSPECTION_1,
                points: 0,
                threshold: 1,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::NEW_PLANTS_1,
                points: 1,
                threshold: 1,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::ANDIE_50,
                points: 5,
                threshold: 50,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::ANDIE_200,
                points: 10,
                threshold: 200,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::ANDIE_500,
                points: 5,
                threshold: 500,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::ANDIE_2000,
                points: 0,
                threshold: 2_000,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::ANDIE_10000,
                points: 0,
                threshold: 10_000,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::CHUN_50,
                points: 5,
                threshold: 50,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::CHUN_200,
                points: 10,
                threshold: 200,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::CHUN_500,
                points: 5,
                threshold: 500,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::CHUN_2000,
                points: 0,
                threshold: 2_000,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::CHUN_10000,
                points: 0,
                threshold: 10_000,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::KUAN_TI_50,
                points: 5,
                threshold: 50,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::KUAN_TI_200,
                points: 10,
                threshold: 200,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::KUAN_TI_500,
                points: 5,
                threshold: 500,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::KUAN_TI_2000,
                points: 0,
                threshold: 2_000,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::KUAN_TI_10000,
                points: 0,
                threshold: 10_000,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::CHAO_50,
                points: 5,
                threshold: 50,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::CHAO_200,
                points: 10,
                threshold: 200,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::CHAO_500,
                points: 5,
                threshold: 500,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::CHAO_2000,
                points: 0,
                threshold: 2_000,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::CHAO_10000,
                points: 0,
                threshold: 10_000,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::ELEESHA_50,
                points: 5,
                threshold: 50,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::ELEESHA_200,
                points: 10,
                threshold: 200,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::ELEESHA_500,
                points: 5,
                threshold: 500,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::ELEESHA_2000,
                points: 0,
                threshold: 2_000,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::ELEESHA_10000,
                points: 0,
                threshold: 10_000,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::GIOELE_50,
                points: 5,
                threshold: 50,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::GIOELE_200,
                points: 10,
                threshold: 200,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::GIOELE_500,
                points: 5,
                threshold: 500,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::GIOELE_2000,
                points: 0,
                threshold: 2_000,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::GIOELE_10000,
                points: 0,
                threshold: 10_000,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::FINOLA_50,
                points: 5,
                threshold: 50,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::FINOLA_200,
                points: 10,
                threshold: 200,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::FINOLA_500,
                points: 5,
                threshold: 500,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::FINOLA_2000,
                points: 0,
                threshold: 2_000,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::FINOLA_10000,
                points: 0,
                threshold: 10_000,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::FRIEDA_50,
                points: 5,
                threshold: 50,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::FRIEDA_200,
                points: 10,
                threshold: 200,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::FRIEDA_500,
                points: 5,
                threshold: 500,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::FRIEDA_2000,
                points: 0,
                threshold: 2_000,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::FRIEDA_10000,
                points: 0,
                threshold: 10_000,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::HUA_50,
                points: 5,
                threshold: 50,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::HUA_200,
                points: 10,
                threshold: 200,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::HUA_500,
                points: 5,
                threshold: 500,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::HUA_2000,
                points: 0,
                threshold: 2_000,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::HUA_10000,
                points: 0,
                threshold: 10_000,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::JANICE_50,
                points: 5,
                threshold: 50,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::JANICE_200,
                points: 10,
                threshold: 200,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::JANICE_500,
                points: 5,
                threshold: 500,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::JANICE_2000,
                points: 0,
                threshold: 2_000,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::JANICE_10000,
                points: 0,
                threshold: 10_000,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::JIN_SU_50,
                points: 5,
                threshold: 50,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::JIN_SU_200,
                points: 10,
                threshold: 200,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::JIN_SU_500,
                points: 5,
                threshold: 500,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::JIN_SU_2000,
                points: 0,
                threshold: 2_000,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::JIN_SU_10000,
                points: 0,
                threshold: 10_000,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::CONTRIBUTIONS_1,
                points: 0,
                threshold: 1,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::CONTRIBUTIONS_5,
                points: 0,
                threshold: 5,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::CONTRIBUTIONS_20,
                points: 0,
                threshold: 20,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::CONTRIBUTIONS_50,
                points: 0,
                threshold: 50,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::CONTRIBUTIONS_100,
                points: 0,
                threshold: 100,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::CONTRIBUTIONS_300,
                points: 0,
                threshold: 300,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::CONTRIBUTIONS_700,
                points: 0,
                threshold: 700,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::CONTRIBUTIONS_1500,
                points: 0,
                threshold: 1500,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::IAN_50,
                points: 5,
                threshold: 50,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::IAN_200,
                points: 10,
                threshold: 200,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::IAN_500,
                points: 5,
                threshold: 500,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::IAN_2000,
                points: 0,
                threshold: 2_000,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::IAN_10000,
                points: 0,
                threshold: 10_000,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::STEPHEN_50,
                points: 5,
                threshold: 50,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::STEPHEN_200,
                points: 10,
                threshold: 200,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::STEPHEN_500,
                points: 5,
                threshold: 500,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::STEPHEN_2000,
                points: 0,
                threshold: 2_000,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::STEPHEN_10000,
                points: 0,
                threshold: 10_000,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::DEREK_50,
                points: 5,
                threshold: 50,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::DEREK_200,
                points: 10,
                threshold: 200,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::DEREK_500,
                points: 5,
                threshold: 500,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::DEREK_2000,
                points: 0,
                threshold: 2_000,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::DEREK_10000,
                points: 0,
                threshold: 10_000,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::PAOLA_50,
                points: 5,
                threshold: 50,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::PAOLA_200,
                points: 10,
                threshold: 200,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::PAOLA_500,
                points: 5,
                threshold: 500,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::PAOLA_2000,
                points: 0,
                threshold: 2_000,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::PAOLA_10000,
                points: 0,
                threshold: 10_000,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::RALUCA_50,
                points: 5,
                threshold: 50,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::RALUCA_200,
                points: 10,
                threshold: 200,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::RALUCA_500,
                points: 5,
                threshold: 500,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::RALUCA_2000,
                points: 0,
                threshold: 2_000,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::RALUCA_10000,
                points: 0,
                threshold: 10_000,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::ROLAND_50,
                points: 5,
                threshold: 50,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::ROLAND_200,
                points: 10,
                threshold: 200,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::ROLAND_500,
                points: 5,
                threshold: 500,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::ROLAND_2000,
                points: 0,
                threshold: 2_000,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::ROLAND_10000,
                points: 0,
                threshold: 10_000,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::TERRENCE_50,
                points: 5,
                threshold: 50,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::TERRENCE_200,
                points: 10,
                threshold: 200,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::TERRENCE_500,
                points: 5,
                threshold: 500,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::TERRENCE_2000,
                points: 0,
                threshold: 2_000,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::TERRENCE_10000,
                points: 0,
                threshold: 10_000,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::EDEN_1,
                points: 5,
                threshold: 1,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::EDEN_5,
                points: 5,
                threshold: 5
            ),
            new AchievementConfigDto(
                name: AchievementEnum::EDEN_20,
                points: 5,
                threshold: 20
            ),
            new AchievementConfigDto(
                name: AchievementEnum::EDEN_50,
                points: 5,
                threshold: 50
            ),
            new AchievementConfigDto(
                name: AchievementEnum::MUSH_CYCLES_50,
                points: 5,
                threshold: 50,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::MUSH_CYCLES_200,
                points: 10,
                threshold: 200,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::MUSH_CYCLES_500,
                points: 5,
                threshold: 500,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::MUSH_CYCLES_2000,
                points: 0,
                threshold: 2_000,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::MUSH_CYCLES_10000,
                points: 0,
                threshold: 10_000,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::EDEN_CONTAMINATED_1,
                points: 10,
                threshold: 1,
            ),
            new AchievementConfigDto(name: AchievementEnum::HUNTER_DOWN_1, points: 1, threshold: 1),
            new AchievementConfigDto(name: AchievementEnum::HUNTER_DOWN_20, points: 1, threshold: 20),
            new AchievementConfigDto(name: AchievementEnum::HUNTER_DOWN_50, points: 1, threshold: 50),
            new AchievementConfigDto(name: AchievementEnum::HUNTER_DOWN_100, points: 1, threshold: 100),
            new AchievementConfigDto(name: AchievementEnum::HUNTER_DOWN_500, points: 1, threshold: 500),
            new AchievementConfigDto(name: AchievementEnum::POLITICIAN_1, points: 1, threshold: 1),
            new AchievementConfigDto(name: AchievementEnum::POLITICIAN_20, points: 1, threshold: 20),
            new AchievementConfigDto(name: AchievementEnum::POLITICIAN_50, points: 1, threshold: 50),
            new AchievementConfigDto(name: AchievementEnum::LIKES_1, points: 0, threshold: 1),
            new AchievementConfigDto(name: AchievementEnum::LIKES_20, points: 0, threshold: 20),
            new AchievementConfigDto(name: AchievementEnum::LIKES_50, points: 0, threshold: 50),
            new AchievementConfigDto(name: AchievementEnum::LIKES_100, points: 0, threshold: 100),
            new AchievementConfigDto(name: AchievementEnum::LIKES_200, points: 0, threshold: 200),
            new AchievementConfigDto(name: AchievementEnum::LIKES_500, points: 0, threshold: 500),
            new AchievementConfigDto(
                name: AchievementEnum::SURGEON_1,
                points: 1,
                threshold: 1,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::BUTCHER_1,
                points: 1,
                threshold: 1,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::COMMUNICATION_EXPERT_1,
                points: 5,
                threshold: 1,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::COMMUNICATION_EXPERT_5,
                points: 5,
                threshold: 5,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::DAY_5_REACHED_1,
                points: 1,
                threshold: 1,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::DAY_10_REACHED_1,
                points: 1,
                threshold: 1,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::DAY_15_REACHED_1,
                points: 1,
                threshold: 1,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::DAY_20_REACHED_1,
                points: 1,
                threshold: 1,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::DAY_30_REACHED_1,
                points: 0,
                threshold: 1,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::MUSH_GENOME_1,
                points: 0,
                threshold: 1
            ),
            new AchievementConfigDto(
                name: AchievementEnum::REBELS_1,
                points: 1,
                threshold: 1,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::PILGRED_IS_BACK_1,
                points: 1,
                threshold: 1,
            ),
            new AchievementConfigDto(name: AchievementEnum::DAY_MAX_3, points: 1, threshold: 3),
            new AchievementConfigDto(name: AchievementEnum::DAY_MAX_4, points: 1, threshold: 4),
            new AchievementConfigDto(name: AchievementEnum::DAY_MAX_5, points: 1, threshold: 5),
            new AchievementConfigDto(name: AchievementEnum::DAY_MAX_6, points: 1, threshold: 6),
            new AchievementConfigDto(name: AchievementEnum::DAY_MAX_7, points: 1, threshold: 7),
            new AchievementConfigDto(name: AchievementEnum::DAY_MAX_8, points: 1, threshold: 8),
            new AchievementConfigDto(name: AchievementEnum::DAY_MAX_9, points: 1, threshold: 9),
            new AchievementConfigDto(name: AchievementEnum::DAY_MAX_10, points: 1, threshold: 10),
            new AchievementConfigDto(name: AchievementEnum::DAY_MAX_11, points: 4, threshold: 11),
            new AchievementConfigDto(name: AchievementEnum::DAY_MAX_12, points: 0, threshold: 12),
            new AchievementConfigDto(name: AchievementEnum::DAY_MAX_13, points: 0, threshold: 13),
            new AchievementConfigDto(name: AchievementEnum::DRUGS_TAKEN_1, points: 1, threshold: 1),
            new AchievementConfigDto(name: AchievementEnum::KIVANC_CONTACTED_1, points: 1, threshold: 1),
        ];
    }

    public static function getByName(AchievementEnum $name): AchievementConfigDto
    {
        if ($name === AchievementEnum::NULL) {
            return new AchievementConfigDto(AchievementEnum::NULL, 0, 0);
        }

        $achievements = array_filter(self::getAll(), static fn (AchievementConfigDto $dto) => $dto->name === $name);
        if (empty($achievements)) {
            throw new \Exception("Achievement {$name->value} not found");
        }

        return current($achievements);
    }
}
