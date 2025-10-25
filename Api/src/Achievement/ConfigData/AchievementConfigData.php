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
