<?php

declare(strict_types=1);

namespace Mush\Skill\ConfigData;

use Mush\Action\Enum\ActionEnum;
use Mush\Modifier\Enum\ModifierNameEnum;
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
                name: SkillEnum::ASTROPHYSICIST,
                modifierConfigs: [
                    ModifierNameEnum::PLAYER_1_MORE_SECTION_REVEALED_ON_ANALYZE_PLANET,
                    ModifierNameEnum::PLAYER_MINUS_1_ACTION_POINT_ON_SCAN,
                ]
            ),
            new SkillConfigDto(
                name: SkillEnum::BOTANIST,
                skillPointsConfig: SkillPointsEnum::BOTANIST_POINTS,
            ),
            new SkillConfigDto(
                name: SkillEnum::CONCEPTOR,
                skillPointsConfig: SkillPointsEnum::CONCEPTOR_POINTS,
            ),
            new SkillConfigDto(
                name: SkillEnum::CONFIDENT,
                actionConfigs: [
                    ActionEnum::CHITCHAT,
                ]
            ),
            new SkillConfigDto(
                name: SkillEnum::CREATIVE,
                modifierConfigs: [
                    ModifierNameEnum::PLAYER_PLUS_1_ACTION_POINT_ON_POST_ACTION_IF_FAILED,
                ],
            ),
            new SkillConfigDto(
                name: SkillEnum::FIREFIGHTER,
                modifierConfigs: [
                    'modifier_for_player_always_success_extinguish',
                ],
                actionConfigs: [
                    ActionEnum::EXTINGUISH_MANUALLY,
                ]
            ),
            new SkillConfigDto(
                name: SkillEnum::IT_EXPERT,
                modifierConfigs: [
                    ModifierNameEnum::DOUBLE_HACK_CHANCE,
                ],
                skillPointsConfig: SkillPointsEnum::IT_EXPERT_POINTS,
            ),
            new SkillConfigDto(
                name: SkillEnum::LEADER,
                actionConfigs: [
                    ActionEnum::MOTIVATIONAL_SPEECH,
                ]
            ),
            new SkillConfigDto(
                name: SkillEnum::MANKIND_ONLY_HOPE,
                modifierConfigs: [
                    'modifier_for_daedalus_+1moral_on_day_change',
                ]
            ),
            new SkillConfigDto(
                name: SkillEnum::MOTIVATOR,
                actionConfigs: [
                    ActionEnum::BORING_SPEECH,
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
                name: SkillEnum::SURVIVALIST,
                modifierConfigs: [
                    ModifierNameEnum::PLAYER_PLUS_1_HEALTH_POINT_ON_CHANGE_VARIABLE_IF_FROM_PLANET_SECTOR_EVENT,
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
