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
                name: AchievementEnum::ARTEFACT_SPECIALIST_1,
                points: 1,
                threshold: 1,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::ARTEFACT_SPECIALIST_2,
                points: 5,
                threshold: 2,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::ARTEFACT_SPECIALIST_3,
                points: 10,
                threshold: 3,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::ARTEFACT_SPECIALIST_4,
                points: 20,
                threshold: 4,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::ARTEFACT_SPECIALIST_5,
                points: 1,
                threshold: 5,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::ARTEFACT_SPECIALIST_6,
                points: 1,
                threshold: 6,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::ARTEFACT_SPECIALIST_7,
                points: 1,
                threshold: 7,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::ARTEFACT_SPECIALIST_8,
                points: 1,
                threshold: 8,
            ),
            new AchievementConfigDto(
                name: AchievementEnum::BACK_TO_ROOT_1,
                points: 1,
                threshold: 1,
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
