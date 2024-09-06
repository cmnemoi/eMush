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
                name: SkillEnum::APPRENTICE,
                actionConfigs: [
                    ActionEnum::LEARN,
                ],
            ),
            new SkillConfigDto(
                name: SkillEnum::ANONYMUSH
            ),
            new SkillConfigDto(
                name: SkillEnum::ASTROPHYSICIST,
                modifierConfigs: [
                    ModifierNameEnum::PLAYER_1_MORE_SECTION_REVEALED_ON_ANALYZE_PLANET,
                    ModifierNameEnum::PLAYER_MINUS_1_ACTION_POINT_ON_SCAN,
                ]
            ),
            new SkillConfigDto(
                name: SkillEnum::BACTEROPHILIAC,
                actionConfigs: [
                    ActionEnum::MAKE_SICK,
                ],
            ),
            new SkillConfigDto(
                name: SkillEnum::BOTANIST,
                skillPointsConfig: SkillPointsEnum::BOTANIST_POINTS,
            ),
            new SkillConfigDto(
                name: SkillEnum::CAFFEINE_JUNKIE,
                modifierConfigs: [
                    ModifierNameEnum::PLAYER_PLUS_2_ACTION_POINTS_ON_CONSUME_ACTION_IF_COFFEE,
                ],
            ),
            new SkillConfigDto(
                name: SkillEnum::CHEF,
                skillPointsConfig: SkillPointsEnum::CHEF_POINTS,
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
                name: SkillEnum::DETERMINED
            ),
            new SkillConfigDto(
                name: SkillEnum::DEFACER,
                actionConfigs: [
                    ActionEnum::DELOG,
                ],
            ),
            new SkillConfigDto(
                name: SkillEnum::DETACHED_CREWMEMBER,
                modifierConfigs: [
                    ModifierNameEnum::PLAYER_SET_0_MORALE_POINT_ON_DEATH,
                ]
            ),
            new SkillConfigDto(
                name: SkillEnum::DEVOTION,
                modifierConfigs: [
                    ModifierNameEnum::PLAYER_PLUS_3_ACTION_POINT_ON_ACCEPT_MISSION,
                ]
            ),
            new SkillConfigDto(
                name: SkillEnum::DIPLOMAT,
                actionConfigs: [
                    ActionEnum::CEASEFIRE,
                ],
            ),
            new SkillConfigDto(
                name: SkillEnum::DISHEARTENING_CONTACT,
                actionConfigs: [
                    ActionEnum::DEPRESS,
                ],
            ),
            new SkillConfigDto(
                name: SkillEnum::EXPERT,
                modifierConfigs: [
                    ModifierNameEnum::PLAYER_PLUS_20_PERCENTAGE_ON_ACTIONS,
                    ModifierNameEnum::PLAYER_PLUS_20_PERCENTAGE_ON_CLUMSINESS,
                    ModifierNameEnum::PLAYER_PLUS_20_PERCENTAGE_ON_DIRTINESS,
                ],
            ),
            new SkillConfigDto(
                name: SkillEnum::FERTILE,
                skillPointsConfig: SkillPointsEnum::SPORE_POINTS,
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
                name: SkillEnum::FRUGIVORE,
                modifierConfigs: [
                    ModifierNameEnum::PLAYER_PLUS_2_ACTION_POINTS_ON_CONSUME_ACTION_IF_ALIEN_FRUIT,
                    ModifierNameEnum::PLAYER_PLUS_1_ACTION_POINTS_ON_CONSUME_ACTION_IF_BANANA,
                ],
            ),
            new SkillConfigDto(
                name: SkillEnum::FUNGAL_KITCHEN,
            ),
            new SkillConfigDto(
                name: SkillEnum::GENIUS,
                actionConfigs: [
                    ActionEnum::BECOME_GENIUS,
                ]
            ),
            new SkillConfigDto(
                name: SkillEnum::GREEN_JELLY,
            ),
            new SkillConfigDto(
                name: SkillEnum::GUNNER,
                modifierConfigs: [
                    ModifierNameEnum::PLAYER_DOUBLE_SUCCESS_RATE_ON_SHOOT_HUNTER,
                    ModifierNameEnum::PLAYER_DOUBLE_DAMAGE_ON_SHOOT_HUNTER,
                ],
            ),
            new SkillConfigDto(
                name: SkillEnum::HARD_BOILED,
                modifierConfigs: [
                    'modifier_for_target_player_+1healthPoint_on_injury',
                ],
            ),
            new SkillConfigDto(
                name: SkillEnum::INFECTOR,
                modifierConfigs: [
                    ModifierNameEnum::PLAYER_PLUS_1_INFECTION,
                ],
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
                name: SkillEnum::LOGISTICS_EXPERT,
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
                name: SkillEnum::MYCELIUM_SPIRIT,
                modifierConfigs: [
                    ModifierNameEnum::DAEDALUS_PLUS_1_MAX_SPORES,
                ],
            ),
            new SkillConfigDto(
                name: SkillEnum::MYCOLOGIST,
                modifierConfigs: [
                    ModifierNameEnum::PLAYER_MINUS_1_SPORE_ON_HEAL,
                ],
            ),
            new SkillConfigDto(
                name: SkillEnum::NERON_ONLY_FRIEND,
            ),
            new SkillConfigDto(
                name: SkillEnum::NURSE,
                skillPointsConfig: SkillPointsEnum::NURSE_POINTS,
            ),
            new SkillConfigDto(
                name: SkillEnum::OBSERVANT,
                modifierConfigs: [
                    ModifierNameEnum::PLAYER_MINUS_1_ACTION_POINT_ON_SEARCH,
                ]
            ),
            new SkillConfigDto(
                name: SkillEnum::OPTIMIST,
                modifierConfigs: [
                    ModifierNameEnum::PLAYER_PLUS_1_MORALE_POINT_ON_DAY_CHANGE,
                ]
            ),
            new SkillConfigDto(
                name: SkillEnum::PHAGOCYTE,
                actionConfigs: [
                    ActionEnum::PHAGOCYTE,
                ],
            ),
            new SkillConfigDto(
                name: SkillEnum::PHYSICIST,
                skillPointsConfig: SkillPointsEnum::PILGRED_POINTS,
            ),
            new SkillConfigDto(
                name: SkillEnum::PILOT,
                modifierConfigs: [
                    'modifier_pilot_always_critical_success_piloting',
                    'modifier_pilot_increased_shoot_hunter_chances',
                ]
            ),
            new SkillConfigDto(
                name: SkillEnum::PRESENTIMENT,
                actionConfigs: [
                    ActionEnum::PREMONITION,
                ]
            ),
            new SkillConfigDto(
                name: SkillEnum::POLITICIAN,
            ),
            new SkillConfigDto(
                name: SkillEnum::PYROMANIAC,
                actionConfigs: [
                    ActionEnum::SPREAD_FIRE,
                ],
            ),
            new SkillConfigDto(
                name: SkillEnum::RADIO_PIRACY,
                actionConfigs: [
                    ActionEnum::SCREW_TALKIE,
                ],
            ),
            new SkillConfigDto(
                name: SkillEnum::SABOTEUR,
                modifierConfigs: [
                    ModifierNameEnum::PLAYER_DOUBLE_PERCENTAGE_ON_SABOTAGE,
                ],
            ),
            new SkillConfigDto(
                name: SkillEnum::SHOOTER,
                skillPointsConfig: SkillPointsEnum::SHOOTER_POINTS,
            ),
            new SkillConfigDto(
                name: SkillEnum::SLIMETRAP,
                actionConfigs: [
                    ActionEnum::SLIME_TRAP,
                ],
            ),
            new SkillConfigDto(
                name: SkillEnum::SHRINK,
                modifierConfigs: [
                    'modifier_for_player_+1morale_point_on_new_cycle_if_lying_down',
                ],
                actionConfigs: [
                    ActionEnum::COMFORT,
                ],
            ),
            new SkillConfigDto(
                name: SkillEnum::SNEAK,
                modifierConfigs: [
                    ModifierNameEnum::PLAYER_MINUS_25_PERCENTAGE_ON_ACTION_HIT_AND_ATTACK,
                ]
            ),
            new SkillConfigDto(
                name: SkillEnum::SOLID,
                modifierConfigs: [
                    ModifierNameEnum::PLAYER_PLUS_1_DAMAGE_ON_HIT,
                ],
                actionConfigs: [
                    ActionEnum::PUT_THROUGH_DOOR,
                ],
            ),
            new SkillConfigDto(
                name: SkillEnum::SPLASHPROOF,
                modifierConfigs: [
                    ModifierNameEnum::PREVENT_MUSH_SHOWER_MALUS,
                ],
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
            new SkillConfigDto(
                name: SkillEnum::TRAPPER,
                actionConfigs: [
                    ActionEnum::TRAP_CLOSET,
                ],
            ),
            new SkillConfigDto(
                name: SkillEnum::TRACKER,
            ),
            new SkillConfigDto(
                name: SkillEnum::TRANSFER,
                actionConfigs: [
                    ActionEnum::EXCHANGE_BODY,
                ],
            ),
            new SkillConfigDto(
                name: SkillEnum::U_TURN,
                actionConfigs: [
                    ActionEnum::RUN_HOME,
                ],
            ),
            new SkillConfigDto(
                name: SkillEnum::VICTIMIZER,
                actionConfigs: [
                    ActionEnum::ANATHEMA,
                ],
            ),
            new SkillConfigDto(
                name: SkillEnum::WRESTLER,
                modifierConfigs: [
                    ModifierNameEnum::PLAYER_PLUS_2_DAMAGE_ON_HIT,
                ],
                actionConfigs: [
                    ActionEnum::PUT_THROUGH_DOOR,
                ],
            ),
            new SkillConfigDto(
                name: SkillEnum::SPRINTER,
                modifierConfigs: [
                    ModifierNameEnum::PLAYER_PLUS_2_MOVEMENT_POINT_ON_EVENT_ACTION_MOVEMENT_CONVERSION_FOR_SPRINTER,
                ],
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
