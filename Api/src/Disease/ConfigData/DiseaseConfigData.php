<?php

declare(strict_types=1);

namespace Mush\Disease\ConfigData;

use Mush\Disease\Dto\DiseaseConfigDto;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Enum\DiseaseEventEnum;
use Mush\Disease\Enum\DisorderEnum;
use Mush\Disease\Enum\InjuryEnum;
use Mush\Disease\Enum\MedicalConditionTypeEnum;
use Mush\Modifier\Enum\ModifierNameEnum;

/** @codeCoverageIgnore */
class DiseaseConfigData
{
    /**
     * @return DiseaseConfigDto[]
     */
    public static function getAll(): array
    {
        return [
            new DiseaseConfigDto(
                key: DiseaseEnum::COLD->toConfigKey('default'),
                name: DiseaseEnum::COLD->toString(),
                type: MedicalConditionTypeEnum::DISEASE,
                duration: [4, 12],
                modifierConfigs: [
                    'modifier_for_player_set_-1actionPoint_on_new_cycle_if_random_20',
                ],
            ),
            new DiseaseConfigDto(
                key: DiseaseEnum::SINUS_STORM->toConfigKey('default'),
                name: DiseaseEnum::SINUS_STORM->toString(),
                type: MedicalConditionTypeEnum::DISEASE,
                canHealNaturally: false,
                modifierConfigs: [
                    'modifier_for_player_set_-1actionPoint_on_new_cycle_if_random_30',
                ],
            ),
            new DiseaseConfigDto(
                key: DiseaseEnum::MIGRAINE->toConfigKey('default'),
                name: DiseaseEnum::MIGRAINE->toString(),
                type: MedicalConditionTypeEnum::DISEASE,
                duration: [1, 4],
                modifierConfigs: [
                    'modifier_for_player_set_-1actionPoint_on_new_cycle_if_random_20',
                ],
            ),
            new DiseaseConfigDto(
                key: DiseaseEnum::VITAMIN_DEFICIENCY->toConfigKey('default'),
                name: DiseaseEnum::VITAMIN_DEFICIENCY->toString(),
                type: MedicalConditionTypeEnum::DISEASE,
                canHealNaturally: false,
                modifierConfigs: [
                    'modifier_for_player_set_-1actionPoint_on_new_cycle_if_random_10',
                ],
            ),
            new DiseaseConfigDto(
                key: DiseaseEnum::EXTREME_TINNITUS->toConfigKey('default'),
                name: DiseaseEnum::EXTREME_TINNITUS->toString(),
                type: MedicalConditionTypeEnum::DISEASE,
                modifierConfigs: [
                    'direct_modifier_player_-2_max_moralPoint',
                    'modifier_for_player_set_-1actionPoint_on_new_cycle_if_random_16',
                ],
            ),
            new DiseaseConfigDto(
                key: DiseaseEnum::ACID_REFLUX->toConfigKey('default'),
                name: DiseaseEnum::ACID_REFLUX->toString(),
                type: MedicalConditionTypeEnum::DISEASE,
                duration: [8, 16],
                modifierConfigs: [
                    'modifier_for_player_set_-2actionPoint_on_post.action_if_reason_consume',
                    'vomiting_consume',
                    'vomiting_move_random_40',
                ],
            ),
            new DiseaseConfigDto(
                key: DiseaseEnum::FLU->toConfigKey('default'),
                name: DiseaseEnum::FLU->toString(),
                type: MedicalConditionTypeEnum::DISEASE,
                duration: [8, 16],
                modifierConfigs: [
                    'direct_modifier_player_-2_max_healthPoint',
                    'direct_modifier_player_-2_max_moralPoint',
                    'modifier_for_player_set_-1actionPoint_on_new_cycle_if_random_20',
                    'modifier_for_player_set_-1healthPoint_on_new_cycle_if_random_10',
                    'cycle_dirtiness_random_40',
                    'vomiting_move_random_40',
                    'vomiting_consume',
                ],
            ),
            new DiseaseConfigDto(
                key: DiseaseEnum::RUBELLA->toConfigKey('default'),
                name: DiseaseEnum::RUBELLA->toString(),
                type: MedicalConditionTypeEnum::DISEASE,
                duration: [16, 32],
                modifierConfigs: [
                    'direct_modifier_player_-1_max_healthPoint',
                    'direct_modifier_player_-1_max_moralPoint',
                    'modifier_for_player_set_-1movementPoint_on_new_cycle_if_random_50',
                ],
            ),
            new DiseaseConfigDto(
                key: DiseaseEnum::GASTROENTERIS->toConfigKey('default'),
                name: DiseaseEnum::GASTROENTERIS->toString(),
                type: MedicalConditionTypeEnum::DISEASE,
                duration: [4, 24],
                modifierConfigs: [
                    'direct_modifier_player_-1_max_healthPoint',
                    'modifier_for_player_set_-1actionPoint_on_post.action_if_reason_consume',
                    'modifier_for_player_set_-1movementPoint_on_new_cycle',
                    'modifier_for_player_set_-1healthPoint_on_new_cycle_if_random_16',
                    'cycle_dirtiness',
                    'vomiting_move_random_40',
                    'vomiting_consume',
                ],
            ),
            new DiseaseConfigDto(
                key: DiseaseEnum::SMALLPOX->toConfigKey('default'),
                name: DiseaseEnum::SMALLPOX->toString(),
                type: MedicalConditionTypeEnum::DISEASE,
                duration: [8, 40],
                modifierConfigs: [
                    'direct_modifier_player_-2_max_healthPoint',
                    'direct_modifier_player_-2_max_moralPoint',
                    'modifier_for_player_set_-1healthPoint_on_new_cycle_if_random_50',
                    'modifier_for_player_set_-2actionPoint_on_new_cycle_if_random_40',
                ],
            ),
            new DiseaseConfigDto(
                key: DiseaseEnum::SYPHILIS->toConfigKey('default'),
                name: DiseaseEnum::SYPHILIS->toString(),
                type: MedicalConditionTypeEnum::DISEASE,
                duration: [40, 120],
                healActionResistance: 2,
                modifierConfigs: [
                    'direct_modifier_player_-2_max_moralPoint',
                    'modifier_for_player_set_-2actionPoint_on_new_cycle_if_random_40',
                    'modifier_for_player_x0.9percentage_on_action_shoot',
                    'breakouts_on_move',
                ],
            ),
            new DiseaseConfigDto(
                key: DiseaseEnum::BLACK_BITE->toConfigKey('default'),
                name: DiseaseEnum::BLACK_BITE->toString(),
                type: MedicalConditionTypeEnum::DISEASE,
                duration: [40, 120],
                modifierConfigs: [
                    'modifier_for_player_set_-1actionPoint_on_new_cycle_if_random_10',
                    'modifier_for_player_set_-4healthPoint_on_infection.player',
                ],
            ),
            new DiseaseConfigDto(
                key: DiseaseEnum::REJUVENATION->toConfigKey('default'),
                name: DiseaseEnum::REJUVENATION->toString(),
                type: MedicalConditionTypeEnum::DISEASE,
                canHealNaturally: false,
                modifierConfigs: [
                    'modifier_for_player_set_-1actionPoint_on_new_cycle_if_random_20',
                    'modifier_for_player_+10percentage_on_cycle_disease',
                    'fitful_sleep_for_player_-1actionPoint_on_new_cycle_if_random_16_if_player_status_lying_down',
                ],
            ),
            new DiseaseConfigDto(
                key: DiseaseEnum::CAT_ALLERGY->toConfigKey('default'),
                name: DiseaseEnum::CAT_ALLERGY->toString(),
                type: MedicalConditionTypeEnum::DISEASE,
                canHealNaturally: false,
                modifierConfigs: [
                    'cat_sneezing_on_move_random16',
                    'cat_allergy_on_take_schrodinger',
                ],
            ),
            new DiseaseConfigDto(
                key: DiseaseEnum::SKIN_INFLAMMATION->toConfigKey('default'),
                name: DiseaseEnum::SKIN_INFLAMMATION->toString(),
                type: MedicalConditionTypeEnum::DISEASE,
                canHealNaturally: false,
                modifierConfigs: [
                    'breakouts_on_move',
                ],
            ),
            new DiseaseConfigDto(
                key: DiseaseEnum::SPACE_RABIES->toConfigKey('default'),
                name: DiseaseEnum::SPACE_RABIES->toString(),
                type: MedicalConditionTypeEnum::DISEASE,
                duration: [24, 50],
                healActionResistance: 2,
                modifierConfigs: [
                    'modifier_for_player_set_-2healthPoint_on_new_cycle',
                    'foaming_on_move',
                    'drooling_on_move',
                    'biting_on_cycle',
                    'psychotic_attacks_on_move',
                ],
            ),
            new DiseaseConfigDto(
                key: DiseaseEnum::MUSH_ALLERGY->toConfigKey('default'),
                name: DiseaseEnum::MUSH_ALLERGY->toString(),
                type: MedicalConditionTypeEnum::DISEASE,
                canHealNaturally: false,
                modifierConfigs: [
                    'modifier_for_player_set_-4healthPoint_on_infection.player',
                    'mush_sneezing',
                ],
            ),
            new DiseaseConfigDto(
                key: DiseaseEnum::QUINCKS_OEDEMA->toConfigKey('default'),
                name: DiseaseEnum::QUINCKS_OEDEMA->toString(),
                type: MedicalConditionTypeEnum::DISEASE,
                duration: [1, 3],
                modifierConfigs: [
                    'direct_modifier_player_-4_max_healthPoint',
                    'modifier_for_player_+1movementPoint_on_move',
                ],
                eventWhenAppeared: DiseaseEventEnum::DEAL_6_DMG_ADD_BURN->toString()
            ),
            new DiseaseConfigDto(
                key: DiseaseEnum::TAPEWORM->toConfigKey('default'),
                name: DiseaseEnum::TAPEWORM->toString(),
                type: MedicalConditionTypeEnum::DISEASE,
                duration: [16, 48],
                modifierConfigs: [
                    'modifier_for_player_set_-1satiety_on_new_cycle',
                ],
            ),
            new DiseaseConfigDto(
                key: DiseaseEnum::SEPSIS->toConfigKey('default'),
                name: DiseaseEnum::SEPSIS->toString(),
                type: MedicalConditionTypeEnum::DISEASE,
                canHealNaturally: false,
                healActionResistance: 2,
                modifierConfigs: [
                    'modifier_for_player_set_-4healthPoint_on_new_cycle',
                ],
            ),
            new DiseaseConfigDto(
                key: DiseaseEnum::FOOD_POISONING->toConfigKey('default'),
                name: DiseaseEnum::FOOD_POISONING->toString(),
                type: MedicalConditionTypeEnum::DISEASE,
                duration: [1, 4],
                modifierConfigs: [
                    'direct_modifier_player_-1_max_healthPoint',
                    'vomiting_consume',
                    'vomiting_move_random_40',
                ],
            ),
            new DiseaseConfigDto(
                key: DiseaseEnum::FUNGIC_INFECTION->toConfigKey('default'),
                name: DiseaseEnum::FUNGIC_INFECTION->toString(),
                type: MedicalConditionTypeEnum::DISEASE,
                duration: [6, 16],
                modifierConfigs: [
                    'direct_modifier_player_-2_max_healthPoint',
                    'direct_modifier_player_-2_max_moralPoint',
                    'cycle_dirtiness',
                ],
            ),
            new DiseaseConfigDto(
                key: DiseaseEnum::SLIGHT_NAUSEA->toConfigKey('default'),
                name: DiseaseEnum::SLIGHT_NAUSEA->toString(),
                type: MedicalConditionTypeEnum::DISEASE,
                duration: [4, 16],
                modifierConfigs: [
                    'modifier_for_player_+1satiety_on_new_cycle',
                    'vomiting_move_random_40',
                ],
            ),
            new DiseaseConfigDto(
                key: DiseaseEnum::JUNKBUMPKINITIS->toConfigKey('default'),
                name: DiseaseEnum::JUNKBUMPKINITIS->toString(),
                type: MedicalConditionTypeEnum::DISEASE,
                duration: [4, 12],
                healActionResistance: 3,
                mushCanHave: true,
                modifierConfigs: [
                    // should give you a pretty little pumpkin head (not likely to be handled through modifiers)
                ],
            ),
            new DiseaseConfigDto(
                key: DisorderEnum::CHRONIC_MIGRAINE->toConfigKey('default'),
                name: DisorderEnum::CHRONIC_MIGRAINE->toString(),
                type: MedicalConditionTypeEnum::DISORDER,
                canHealNaturally: false,
                healActionResistance: 2,
                modifierConfigs: [
                    'direct_modifier_player_-2_max_moralPoint',
                    'modifier_for_player_set_-1actionPoint_on_new_cycle_if_random_16',
                ],
                removeLower: [
                    DiseaseEnum::MIGRAINE->toString(),
                ],
            ),
            new DiseaseConfigDto(
                key: DisorderEnum::DEPRESSION->toConfigKey('default'),
                name: DisorderEnum::DEPRESSION->toString(),
                type: MedicalConditionTypeEnum::DISORDER,
                canHealNaturally: false,
                healActionResistance: 4,
                modifierConfigs: [
                    'direct_modifier_player_-2_max_actionPoint',
                    'direct_modifier_player_-2_max_moralPoint',
                ],
            ),
            new DiseaseConfigDto(
                key: DisorderEnum::PSYCHOTIC_EPISODE->toConfigKey('default'),
                name: DisorderEnum::PSYCHOTIC_EPISODE->toString(),
                type: MedicalConditionTypeEnum::DISORDER,
                canHealNaturally: false,
                healActionResistance: 6,
                modifierConfigs: [
                    'biting_on_cycle',
                    'psychotic_attacks_on_move',
                ],
            ),
            new DiseaseConfigDto(
                key: DisorderEnum::AGORAPHOBIA->toConfigKey('default'),
                name: DisorderEnum::AGORAPHOBIA->toString(),
                type: MedicalConditionTypeEnum::DISORDER,
                canHealNaturally: false,
                healActionResistance: 2,
                modifierConfigs: [
                    'modifier_for_player_+1actionPoint_on_actions_if_player_in_room_four_people',
                    'modifier_for_player_+1movementPoint_on_move_action_if_player_in_room_four_people',
                    'prevent_piloting_actions',
                ],
            ),
            new DiseaseConfigDto(
                key: DisorderEnum::AILUROPHOBIA->toConfigKey('default'),
                name: DisorderEnum::AILUROPHOBIA->toString(),
                type: MedicalConditionTypeEnum::DISORDER,
                canHealNaturally: false,
                healActionResistance: 2,
                modifierConfigs: [
                    'modifier_for_player_+2movementPoint_on_move_if_item_in_room_schrodinger',
                    'modifier_for_player_+2actionPoint_on_actions_if_item_in_room_schrodinger_if_not_reason_move',
                    'fear_of_cat_on_move',
                ],
            ),
            new DiseaseConfigDto(
                key: DisorderEnum::CRABISM->toConfigKey('default'),
                name: DisorderEnum::CRABISM->toString(),
                type: MedicalConditionTypeEnum::DISORDER,
                canHealNaturally: false,
                modifierConfigs: [
                    'direct_modifier_player_-4_max_moralPoint',
                    'run_in_circles_for_player_set_-2movementPoint_on_new_cycle_if_random_16',
                    'wall_head_bang_for_player_set_-1healthPoint_on_new_cycle_if_random_16',
                    'screaming_for_player_set_-1actionPoint_on_new_cycle_if_random_16',
                ],
            ),
            new DiseaseConfigDto(
                key: DisorderEnum::COPROLALIA->toConfigKey('default'),
                name: DisorderEnum::COPROLALIA->toString(),
                type: MedicalConditionTypeEnum::DISORDER,
                canHealNaturally: false,
                modifierConfigs: [
                    'direct_modifier_player_-4_max_moralPoint',
                    'coprolalia_ON_new_message_default',
                ],
            ),
            new DiseaseConfigDto(
                key: DisorderEnum::CHRONIC_VERTIGO->toConfigKey('default'),
                name: DisorderEnum::CHRONIC_VERTIGO->toString(),
                type: MedicalConditionTypeEnum::DISORDER,
                canHealNaturally: false,
                healActionResistance: 2,
                modifierConfigs: [
                    'prevent_piloting_actions',
                ],
                removeLower: [
                    DisorderEnum::VERTIGO->toString(),
                ],
            ),
            new DiseaseConfigDto(
                key: DisorderEnum::VERTIGO->toConfigKey('default'),
                name: DisorderEnum::VERTIGO->toString(),
                type: MedicalConditionTypeEnum::DISORDER,
                duration: [2, 4],
                modifierConfigs: [
                    'prevent_piloting_actions',
                ],
            ),
            new DiseaseConfigDto(
                key: DisorderEnum::WEAPON_PHOBIA->toConfigKey('default'),
                name: DisorderEnum::WEAPON_PHOBIA->toString(),
                type: MedicalConditionTypeEnum::DISORDER,
                canHealNaturally: false,
                modifierConfigs: [
                    'prevent_attack_actions',
                    'prevent_shoot_actions',
                ],
            ),
            new DiseaseConfigDto(
                key: DisorderEnum::PARANOIA->toConfigKey('default'),
                name: DisorderEnum::PARANOIA->toString(),
                type: MedicalConditionTypeEnum::DISORDER,
                canHealNaturally: false,
                healActionResistance: 4,
                modifierConfigs: [
                    'direct_modifier_player_-3_max_moralPoint',
                    'paranoia_ON_new_message_default',
                    'paranoia_ON_read_message_default',
                ],
            ),
            new DiseaseConfigDto(
                key: DisorderEnum::SPLEEN->toConfigKey('default'),
                name: DisorderEnum::SPLEEN->toString(),
                type: MedicalConditionTypeEnum::DISORDER,
                duration: [4, 24],
                healActionResistance: 6,
                modifierConfigs: [
                    'modifier_for_player_set_-1moralPoint_on_new_cycle_if_random_70',
                ],
            ),
            new DiseaseConfigDto(
                key: InjuryEnum::BROKEN_FOOT->toConfigKey('default'),
                name: InjuryEnum::BROKEN_FOOT->toString(),
                type: MedicalConditionTypeEnum::INJURY,
                modifierConfigs: [
                    'direct_modifier_player_-1_max_healthPoint',
                    'modifier_for_player_+1movementPoint_on_move',
                ],
            ),
            new DiseaseConfigDto(
                key: InjuryEnum::MASHED_FOOT->toConfigKey('default'),
                name: InjuryEnum::MASHED_FOOT->toString(),
                type: MedicalConditionTypeEnum::INJURY,
                modifierConfigs: [
                    'direct_modifier_player_-1_max_healthPoint',
                    'direct_modifier_player_-3_max_movementPoint',
                    'modifier_for_player_+1movementPoint_on_move',
                ],
                removeLower: [
                    InjuryEnum::BROKEN_FOOT->toString(),
                ],
            ),
            new DiseaseConfigDto(
                key: InjuryEnum::BROKEN_LEG->toConfigKey('default'),
                name: InjuryEnum::BROKEN_LEG->toString(),
                type: MedicalConditionTypeEnum::INJURY,
                modifierConfigs: [
                    'direct_modifier_player_-1_max_healthPoint',
                    'direct_modifier_player_-5_max_movementPoint',
                    'modifier_for_player_+1movementPoint_on_move',
                ],
            ),
            new DiseaseConfigDto(
                key: InjuryEnum::MASHED_LEGS->toConfigKey('default'),
                name: InjuryEnum::MASHED_LEGS->toString(),
                type: MedicalConditionTypeEnum::INJURY,
                modifierConfigs: [
                    'direct_modifier_player_-4_max_healthPoint',
                    'direct_modifier_player_-12_max_movementPoint',
                    'prevent_move',
                ],
                removeLower: [
                    InjuryEnum::BROKEN_LEG->toString(),
                    InjuryEnum::BROKEN_FOOT->toString(),
                    InjuryEnum::MASHED_FOOT->toString(),
                ],
                eventWhenAppeared: DiseaseEventEnum::ADD_CRITICAL_HAEMORRHAGE_100->toString()
            ),
            new DiseaseConfigDto(
                key: InjuryEnum::BURNS_50_OF_BODY->toConfigKey('default'),
                name: InjuryEnum::BURNS_50_OF_BODY->toString(),
                type: MedicalConditionTypeEnum::INJURY,
                modifierConfigs: [
                    'modifier_for_player_+10percentage_on_cycle_disease',
                    'modifier_for_player_+1actionPoint',
                ],
            ),
            new DiseaseConfigDto(
                key: InjuryEnum::BURNS_90_OF_BODY->toConfigKey('default'),
                name: InjuryEnum::BURNS_90_OF_BODY->toString(),
                type: MedicalConditionTypeEnum::INJURY,
                modifierConfigs: [
                    'direct_modifier_player_-4_max_healthPoint',
                    'modifier_for_player_+10percentage_on_cycle_disease',
                    'septicemia_post_action',
                    'septicemia_on_dirty',
                    'septicemia_cycle_change',
                ],
                removeLower: [
                    InjuryEnum::BURNS_50_OF_BODY->toString(),
                ],
            ),
            new DiseaseConfigDto(
                key: InjuryEnum::HEAD_TRAUMA->toConfigKey('default'),
                name: InjuryEnum::HEAD_TRAUMA->toString(),
                type: MedicalConditionTypeEnum::INJURY,
                modifierConfigs: [
                    'direct_modifier_player_-3_max_healthPoint',
                    'direct_modifier_player_-3_max_moralPoint',
                    'septicemia_post_action',
                    'septicemia_on_dirty',
                    'septicemia_cycle_change',
                ],
            ),
            new DiseaseConfigDto(
                key: InjuryEnum::OPEN_AIR_BRAIN->toConfigKey('default'),
                name: InjuryEnum::OPEN_AIR_BRAIN->toString(),
                type: MedicalConditionTypeEnum::INJURY,
                modifierConfigs: [
                    'direct_modifier_player_-5_max_healthPoint',
                    'direct_modifier_player_-2_max_moralPoint',
                    'septicemia_post_action',
                    'septicemia_on_dirty',
                    'septicemia_cycle_change',
                ],
                removeLower: [
                    InjuryEnum::HEAD_TRAUMA->toString(),
                ],
            ),
            new DiseaseConfigDto(
                key: InjuryEnum::BROKEN_FINGER->toConfigKey('default'),
                name: InjuryEnum::BROKEN_FINGER->toString(),
                type: MedicalConditionTypeEnum::INJURY,
                modifierConfigs: [
                    'modifier_for_player_+1actionPoint',
                ],
            ),
            new DiseaseConfigDto(
                key: InjuryEnum::MISSING_FINGER->toConfigKey('default'),
                name: InjuryEnum::MISSING_FINGER->toString(),
                type: MedicalConditionTypeEnum::INJURY,
                modifierConfigs: [
                    'direct_modifier_player_-1_max_healthPoint',
                    'modifier_for_player_+1actionPoint',
                ],
                removeLower: [
                    InjuryEnum::BROKEN_FINGER->toString(),
                ],
                eventWhenAppeared: DiseaseEventEnum::ADD_HAEMORRHAGE_20->toString()
            ),
            new DiseaseConfigDto(
                key: InjuryEnum::BURNT_HAND->toConfigKey('default'),
                name: InjuryEnum::BURNT_HAND->toString(),
                type: MedicalConditionTypeEnum::INJURY,
                modifierConfigs: [
                    'direct_modifier_player_-1_max_healthPoint',
                    'modifier_for_player_+1actionPoint',
                ],
            ),
            new DiseaseConfigDto(
                key: InjuryEnum::MASHED_HAND->toConfigKey('default'),
                name: InjuryEnum::MASHED_HAND->toString(),
                type: MedicalConditionTypeEnum::INJURY,
                modifierConfigs: [
                    'direct_modifier_player_-2_max_healthPoint',
                    'modifier_for_player_x0.6percentage_on_action_shoot',
                    'modifier_for_player_+2actionPoint',
                ],
                removeLower: [
                    InjuryEnum::BROKEN_FINGER->toString(),
                    InjuryEnum::MISSING_FINGER->toString(),
                    InjuryEnum::BURNT_HAND->toString(),
                ],
            ),
            new DiseaseConfigDto(
                key: InjuryEnum::BUSTED_ARM_JOINT->toConfigKey('default'),
                name: InjuryEnum::BUSTED_ARM_JOINT->toString(),
                type: MedicalConditionTypeEnum::INJURY,
                modifierConfigs: [
                    'direct_modifier_player_-4_max_healthPoint',
                    'modifier_for_player_x0.6percentage_on_action_shoot',
                    'modifier_for_player_+2actionPoint',
                ],
            ),
            new DiseaseConfigDto(
                key: InjuryEnum::BURNT_ARMS->toConfigKey('default'),
                name: InjuryEnum::BURNT_ARMS->toString(),
                type: MedicalConditionTypeEnum::INJURY,
                modifierConfigs: [
                    'direct_modifier_player_-1_max_healthPoint',
                    'modifier_for_player_+10percentage_on_cycle_disease',
                    'modifier_for_player_+2actionPoint',
                    'modifier_for_player_x0.8percentage_on_action_shoot',
                ],
                removeLower: [
                    InjuryEnum::BURNT_HAND->toString(),
                ],
            ),
            new DiseaseConfigDto(
                key: InjuryEnum::MASHED_ARMS->toConfigKey('default'),
                name: InjuryEnum::MASHED_ARMS->toString(),
                type: MedicalConditionTypeEnum::INJURY,
                modifierConfigs: [
                    'direct_modifier_player_-3_max_healthPoint',
                    'modifier_for_player_x0.6percentage_on_action_shoot',
                    'modifier_for_player_+3actionPoint',
                ],
                removeLower: [
                    InjuryEnum::BROKEN_FINGER->toString(),
                    InjuryEnum::MISSING_FINGER->toString(),
                    InjuryEnum::BURNT_HAND->toString(),
                    InjuryEnum::MASHED_HAND->toString(),
                    InjuryEnum::BUSTED_ARM_JOINT->toString(),
                    InjuryEnum::BURNT_ARMS->toString(),
                ],
            ),
            new DiseaseConfigDto(
                key: InjuryEnum::BRUISED_SHOULDER->toConfigKey('default'),
                name: InjuryEnum::BRUISED_SHOULDER->toString(),
                type: MedicalConditionTypeEnum::INJURY,
                modifierConfigs: [
                    'direct_modifier_player_-1_max_healthPoint',
                    'modifier_for_player_x0.9percentage_on_action_shoot',
                ],
            ),
            new DiseaseConfigDto(
                key: InjuryEnum::BROKEN_SHOULDER->toConfigKey('default'),
                name: InjuryEnum::BROKEN_SHOULDER->toString(),
                type: MedicalConditionTypeEnum::INJURY,
                modifierConfigs: [
                    'direct_modifier_player_-2_max_healthPoint',
                    'modifier_for_player_x0.8percentage_on_action_shoot',
                    'prevent_pick_heavy_item',
                ],
                removeLower: [
                    InjuryEnum::BRUISED_SHOULDER->toString(),
                ],
                eventWhenAppeared: DiseaseEventEnum::DROP_HEAVY_ITEMS->toString()
            ),
            new DiseaseConfigDto(
                key: InjuryEnum::BUSTED_SHOULDER->toConfigKey('default'),
                name: InjuryEnum::BUSTED_SHOULDER->toString(),
                type: MedicalConditionTypeEnum::INJURY,
                modifierConfigs: [
                    'direct_modifier_player_-3_max_healthPoint',
                    'modifier_for_player_x0.6percentage_on_action_shoot',
                    'modifier_for_player_+2actionPoint',
                    'prevent_pick_heavy_item',
                ],
                removeLower: [
                    InjuryEnum::BRUISED_SHOULDER->toString(),
                    InjuryEnum::BROKEN_SHOULDER->toString(),
                    InjuryEnum::MASHED_ARMS->toString(),
                    InjuryEnum::BROKEN_FINGER->toString(),
                    InjuryEnum::MISSING_FINGER->toString(),
                    InjuryEnum::BURNT_HAND->toString(),
                    InjuryEnum::MASHED_HAND->toString(),
                    InjuryEnum::BUSTED_ARM_JOINT->toString(),
                    InjuryEnum::BURNT_ARMS->toString(),
                ],
                eventWhenAppeared: DiseaseEventEnum::DROP_HEAVY_ITEMS->toString()
            ),
            new DiseaseConfigDto(
                key: InjuryEnum::HAEMORRHAGE->toConfigKey('default'),
                name: InjuryEnum::HAEMORRHAGE->toString(),
                type: MedicalConditionTypeEnum::INJURY,
                modifierConfigs: [
                    'direct_modifier_player_-1_max_healthPoint',
                    'modifier_for_player_set_-1healthPoint_on_new_cycle',
                ],
            ),
            new DiseaseConfigDto(
                key: InjuryEnum::CRITICAL_HAEMORRHAGE->toConfigKey('default'),
                name: InjuryEnum::CRITICAL_HAEMORRHAGE->toString(),
                type: MedicalConditionTypeEnum::INJURY,
                modifierConfigs: [
                    'direct_modifier_player_-2_max_healthPoint',
                    'modifier_for_player_set_-2healthPoint_on_new_cycle',
                ],
                removeLower: [
                    InjuryEnum::HAEMORRHAGE->toString(),
                ],
            ),
            new DiseaseConfigDto(
                key: InjuryEnum::IMPLANTED_BULLET->toConfigKey('default'),
                name: InjuryEnum::IMPLANTED_BULLET->toString(),
                type: MedicalConditionTypeEnum::INJURY,
                modifierConfigs: [
                    'direct_modifier_player_-2_max_healthPoint',
                    'modifier_for_player_+1actionPoint',
                    'foaming_on_move',
                ],
            ),
            new DiseaseConfigDto(
                key: InjuryEnum::DYSFUNCTIONAL_LIVER->toConfigKey('default'),
                name: InjuryEnum::DYSFUNCTIONAL_LIVER->toString(),
                type: MedicalConditionTypeEnum::INJURY,
                modifierConfigs: [
                    'direct_modifier_player_-2_max_healthPoint',
                    'modifier_for_player_set_-2actionPoint_on_post.action_if_reason_consume',
                    'modifier_for_player_+1actionPoint',
                    'vomiting_consume',
                    'vomiting_move_random_40',
                    'drooling_on_move',
                ],
            ),
            new DiseaseConfigDto(
                key: InjuryEnum::PUNCTURED_LUNG->toConfigKey('default'),
                name: InjuryEnum::PUNCTURED_LUNG->toString(),
                type: MedicalConditionTypeEnum::INJURY,
                modifierConfigs: [
                    'direct_modifier_player_-2_max_healthPoint',
                    'modifier_for_player_+2actionPoint',
                    'mute_prevent_messages',
                    'prevent_spoken_actions',
                    'modifier_for_player_set_-2healthPoint_on_new_cycle',
                ],
            ),
            new DiseaseConfigDto(
                key: InjuryEnum::BROKEN_RIBS->toConfigKey('default'),
                name: InjuryEnum::BROKEN_RIBS->toString(),
                type: MedicalConditionTypeEnum::INJURY,
                modifierConfigs: [
                    'direct_modifier_player_-1_max_healthPoint',
                    'modifier_for_player_x0.8percentage_on_action_shoot',
                    'modifier_for_player_+1actionPoint',
                    'modifier_for_player_+1movementPoint_on_move',
                ],
            ),
            new DiseaseConfigDto(
                key: InjuryEnum::TORN_TONGUE->toConfigKey('default'),
                name: InjuryEnum::TORN_TONGUE->toString(),
                type: MedicalConditionTypeEnum::INJURY,
                modifierConfigs: [
                    'direct_modifier_player_-1_max_healthPoint',
                    'mute_prevent_messages',
                    'prevent_spoken_actions',
                ],
            ),
            new DiseaseConfigDto(
                key: InjuryEnum::BURST_NOSE->toConfigKey('default'),
                name: InjuryEnum::BURST_NOSE->toString(),
                type: MedicalConditionTypeEnum::INJURY,
                modifierConfigs: [
                    'direct_modifier_player_-2_max_healthPoint',
                    'direct_modifier_player_-2_max_moralPoint',
                ],
            ),
            new DiseaseConfigDto(
                key: InjuryEnum::INNER_EAR_DAMAGED->toConfigKey('default'),
                name: InjuryEnum::INNER_EAR_DAMAGED->toString(),
                type: MedicalConditionTypeEnum::INJURY,
                modifierConfigs: [
                    'modifier_for_player_+1actionPoint',
                    'modifier_for_player_+1movementPoint_on_move',
                    ModifierNameEnum::DISEASE_SHOOT_ACTION_REDUCED_SUCCESS,
                    'prevent_piloting_actions',
                ],
            ),
            new DiseaseConfigDto(
                key: InjuryEnum::DAMAGED_EARS->toConfigKey('default'),
                name: InjuryEnum::DAMAGED_EARS->toString(),
                type: MedicalConditionTypeEnum::INJURY,
                modifierConfigs: [
                    'direct_modifier_player_-2_max_healthPoint',
                    'deaf_ON_new_message_default',
                    'deaf_ON_read_message_default',
                ],
            ),
            new DiseaseConfigDto(
                key: InjuryEnum::DESTROYED_EARS->toConfigKey('default'),
                name: InjuryEnum::DESTROYED_EARS->toString(),
                type: MedicalConditionTypeEnum::INJURY,
                modifierConfigs: [
                    'direct_modifier_player_-3_max_healthPoint',
                    'direct_modifier_player_-1_max_moralPoint',
                    'deaf_ON_new_message_default',
                    'deaf_ON_read_message_default',
                ],
                removeLower: [
                    InjuryEnum::DAMAGED_EARS->toString(),
                ],
            ),
        ];
    }

    public static function getByName(string $name): DiseaseConfigDto
    {
        $data = current(array_filter(
            self::getAll(),
            static fn (DiseaseConfigDto $data) => $data->name === $name
        ));

        if (!$data) {
            throw new \Exception(\sprintf('Disease config %s not found', $name));
        }

        return $data;
    }
}
