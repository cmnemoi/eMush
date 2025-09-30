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
                name: AchievementEnum::PLANET_SCANNED_1,
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
