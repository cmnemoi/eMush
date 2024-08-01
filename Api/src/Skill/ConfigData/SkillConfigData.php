<?php

declare(strict_types=1);

namespace Mush\Skill\ConfigData;

use Mush\Action\Enum\ActionEnum;
use Mush\Skill\Dto\SkillConfigDto;
use Mush\Skill\Enum\SkillEnum;
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
                name: SkillEnum::CONCEPTOR,
                skillPointsConfig: SkillPointsEnum::CONCEPTOR_POINTS,
            ),
            new SkillConfigDto(
                name: SkillEnum::MANKIND_ONLY_HOPE,
                modifierConfigs: [
                    'modifier_for_daedalus_+1moral_on_day_change',
                ]
            ),
            new SkillConfigDto(
                name: SkillEnum::PILOT,
                modifierConfigs: [
                    'modifier_pilot_always_critical_success_piloting',
                    'modifier_pilot_increased_shoot_hunter_chances',
                ]
            ),
            new SkillConfigDto(
                name: SkillEnum::SHOOTER,
                skillPointsConfig: SkillPointsEnum::SHOOTER_POINTS,
            ),
            new SkillConfigDto(
                name: SkillEnum::SHRINK,
                actionConfigs: [
                    ActionEnum::COMFORT,
                ]
            ),
            new SkillConfigDto(
                name: SkillEnum::TECHNICIAN,
                modifierConfigs: [
                    'modifier_technician_double_repair_and_renovate_chance',
                ],
                skillPointsConfig: SkillPointsEnum::TECHNICIAN_POINTS,
            ),
        ];
    }

    public static function getByName(SkillEnum $name): SkillConfigDto
    {
        return current(
            array_filter(
                self::getAll(),
                static fn (SkillConfigDto $skillConfigDto) => $skillConfigDto->name === $name
            )
        );
    }
}
