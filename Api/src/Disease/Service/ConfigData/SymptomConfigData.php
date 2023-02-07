<?php

namespace Mush\Disease\Service\ConfigData;

/** @codeCoverageIgnore */
class SymptomConfigData
{
    public static array $dataArray = [
        [
            'name' => 'biting_ON_new_cycle_default',
            'symptomName' => 'biting',
            'trigger' => 'new_cycle',
            'visibility' => 'public',
            'symptomActivationRequirements' => [
                'player_in_room_not_alone',
                'random_16',
            ],
        ],
        [
            'name' => 'breakouts_ON_post.action_default',
            'symptomName' => 'breakouts',
            'trigger' => 'post.action',
            'visibility' => 'public',
            'symptomActivationRequirements' => [
                'random_16',
                'reason_move',
            ],
        ],
        [
            'name' => 'cat_allergy_ON_post.action_default',
            'symptomName' => 'cat_allergy',
            'trigger' => 'post.action',
            'visibility' => 'public',
            'symptomActivationRequirements' => [
                'player_equipment_schrodinger',
                'reason_take',
            ],
        ],
        [
            'name' => 'sneezing_ON_post.action_cat_default',
            'symptomName' => 'sneezing',
            'trigger' => 'post.action',
            'visibility' => 'public',
            'symptomActivationRequirements' => [
                'reason_move',
                'item_in_room_schrodinger',
            ],
        ],
        [
            'name' => 'vomiting_ON_post.action_consume_drug_default',
            'symptomName' => 'vomiting',
            'trigger' => 'post.action',
            'visibility' => 'secret',
            'symptomActivationRequirements' => [
                'reason_consume_drug',
            ],
        ],
        [
            'name' => 'vomiting_ON_post.action_consume_default',
            'symptomName' => 'vomiting',
            'trigger' => 'post.action',
            'visibility' => 'secret',
            'symptomActivationRequirements' => [
                'reason_consume',
            ],
        ],
        [
            'name' => 'dirtiness_ON_new_cycle_default',
            'symptomName' => 'dirtiness',
            'trigger' => 'new_cycle',
            'visibility' => 'hidden',
            'symptomActivationRequirements' => [],
        ],
        [
            'name' => 'dirtiness_ON_new_cycle_random_40_default',
            'symptomName' => 'dirtiness',
            'trigger' => 'new_cycle',
            'visibility' => 'hidden',
            'symptomActivationRequirements' => [
                'random_40',
            ],
        ],
        [
            'name' => 'drooling_ON_post.action_default',
            'symptomName' => 'drooling',
            'trigger' => 'post.action',
            'visibility' => 'public',
            'symptomActivationRequirements' => [
                'random_16',
                'reason_move',
            ],
        ],
        [
            'name' => 'foaming_mouth_ON_post.action_default',
            'symptomName' => 'foaming_mouth',
            'trigger' => 'post.action',
            'visibility' => 'public',
            'symptomActivationRequirements' => [
                'random_16',
                'reason_move',
            ],
        ],
        [
            'name' => 'vomiting_ON_post.action_move_default',
            'symptomName' => 'vomiting',
            'trigger' => 'post.action',
            'visibility' => 'public',
            'symptomActivationRequirements' => [
                'reason_move',
                'random_16',
            ],
        ],
        [
            'name' => 'sneezing_ON_post.action_mush_default',
            'symptomName' => 'sneezing',
            'trigger' => 'post.action',
            'visibility' => 'public',
            'symptomActivationRequirements' => [
                'player_in_room_mush_in_room',
                'reason_move',
                'random_16',
            ],
        ],
        [
            'name' => 'cant_move_ON_move_default',
            'symptomName' => 'cant_move',
            'trigger' => 'move',
            'visibility' => 'public',
            'symptomActivationRequirements' => [],
        ],
        [
            'name' => 'cant_pick_up_heavy_items_ON_take_default',
            'symptomName' => 'cant_pick_up_heavy_items',
            'trigger' => 'take',
            'visibility' => 'public',
            'symptomActivationRequirements' => [
                'item_status_heavy',
            ],
        ],
        [
            'name' => 'deaf_ON_new_message_default',
            'symptomName' => 'deaf',
            'trigger' => 'new_message',
            'visibility' => 'public',
            'symptomActivationRequirements' => [],
        ],
        [
            'name' => 'septicemia_ON_new_cycle_default',
            'symptomName' => 'septicemia',
            'trigger' => 'new_cycle',
            'visibility' => 'public',
            'symptomActivationRequirements' => [
                'player_status_dirty',
            ],
        ],
        [
            'name' => 'septicemia_ON_status.applied_default',
            'symptomName' => 'septicemia',
            'trigger' => 'status.applied',
            'visibility' => 'public',
            'symptomActivationRequirements' => [
                'player_status_dirty',
            ],
        ],
        [
            'name' => 'septicemia_ON_post.action_default',
            'symptomName' => 'septicemia',
            'trigger' => 'post.action',
            'visibility' => 'public',
            'symptomActivationRequirements' => [
                'action_dirty_rate',
                'player_status_dirty',
            ],
        ],
        [
            'name' => 'mute_ON_action_spoken_default',
            'symptomName' => 'mute',
            'trigger' => 'action_spoken',
            'visibility' => 'public',
            'symptomActivationRequirements' => [],
        ],
        [
            'name' => 'fear_of_cats_ON_post.action_default',
            'symptomName' => 'fear_of_cats',
            'trigger' => 'post.action',
            'visibility' => 'public',
            'symptomActivationRequirements' => [
                'item_in_room_schrodinger',
                'reason_move',
                'random_50',
            ],
        ],
        [
            'name' => 'no_attack_actions_ON_action_attack_default',
            'symptomName' => 'no_attack_actions',
            'trigger' => 'action_attack',
            'visibility' => 'public',
            'symptomActivationRequirements' => [],
        ],
        [
            'name' => 'no_piloting_actions_ON_action_pilot_default',
            'symptomName' => 'no_piloting_actions',
            'trigger' => 'action_pilot',
            'visibility' => 'public',
            'symptomActivationRequirements' => [],
        ],
        [
            'name' => 'no_shoot_actions_ON_action_shoot_default',
            'symptomName' => 'no_shoot_actions',
            'trigger' => 'action_shoot',
            'visibility' => 'public',
            'symptomActivationRequirements' => [],
        ],
        [
            'name' => 'psychotic_attacks_ON_post.action_default',
            'symptomName' => 'psychotic_attacks',
            'trigger' => 'post.action',
            'visibility' => 'public',
            'symptomActivationRequirements' => [
                'reason_move',
                'random_16',
            ],
        ],
        [
            'name' => 'coprolalia_messages_ON_new_message_default',
            'symptomName' => 'coprolalia_messages',
            'trigger' => 'new_message',
            'visibility' => 'public',
            'symptomActivationRequirements' => [],
        ],
        [
            'name' => 'paranoia_messages_ON_new_message_default',
            'symptomName' => 'paranoia_messages',
            'trigger' => 'new_message',
            'visibility' => 'public',
            'symptomActivationRequirements' => [],
        ],
    ];
}
