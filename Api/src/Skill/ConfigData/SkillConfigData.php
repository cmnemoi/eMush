<?php

declare(strict_types=1);

namespace Mush\Skill\ConfigData;

use Mush\Skill\Dto\SkillConfigDto;
use Mush\Skill\Enum\SkillName;

/**
 * @codeCoverageIgnore
 */
abstract class SkillConfigData
{
    /**
     * @return SkillConfigDto[]
     */
    public static function getAll(): array
    {
        return [
            new SkillConfigDto(
                name: SkillName::PILOT,
            ),
        ];
    }

    public static function getByName(SkillName $name): SkillConfigDto
    {
        return current(
            array_filter(
                self::getAll(),
                static fn (SkillConfigDto $skillConfigDto) => $skillConfigDto->name === $name
            )
        );
    }
}
