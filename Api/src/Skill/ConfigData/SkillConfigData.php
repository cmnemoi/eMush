<?php

declare(strict_types=1);

namespace Mush\Skill\ConfigData;

use Mush\Skill\Dto\SkillConfigDto;
use Mush\Skill\Enum\SkillName;
use Mush\Status\Enum\SkillPointsEnum;

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
                name: SkillName::CONCEPTOR,
                skillPointsConfig: SkillPointsEnum::CONCEPTOR_POINTS,
            ),
            new SkillConfigDto(
                name: SkillName::MANKIND_ONLY_HOPE,
                modifierConfigs: [
                    'modifier_for_daedalus_+1moral_on_day_change',
                ]
            ),
            new SkillConfigDto(
                name: SkillName::PILOT,
                modifierConfigs: [
                    'modifier_pilot_always_critical_success_piloting',
                    'modifier_pilot_increased_shoot_hunter_chances',
                ]
            ),
            new SkillConfigDto(
                name: SkillName::SHOOTER,
                skillPointsConfig: SkillPointsEnum::SHOOTER_POINTS,
            ),
            new SkillConfigDto(
                name: SkillName::SHRINK,
            ),
            new SkillConfigDto(
                name: SkillName::TECHNICIAN,
                modifierConfigs: [
                    'modifier_technician_double_repair_and_renovate_chance',
                ],
                skillPointsConfig: SkillPointsEnum::TECHNICIAN_POINTS,
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
