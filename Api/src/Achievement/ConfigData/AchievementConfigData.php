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
