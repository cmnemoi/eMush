<?php

namespace Mush\Disease\ConfigData;

use Mush\Action\Enum\ActionEnum;
use Mush\Modifier\Enum\ModifierRequirementEnum;

/** @codeCoverageIgnore */
class SymptomConfigData
{
    public static array $dataArray = [
        [
            'name' => 'biting_ON_new_cycle_default',
            'symptomName' => 'biting',
            'trigger' => 'new_cycle',
            'visibility' => 'public',
            'modifierActivationRequirements' => [
                'player_in_room_not_alone',
                'random_16',
            ],
            'tagConstraints' => [],
        ],
        [
            'name' => 'breakouts_ON_post.action_default',
            'symptomName' => 'breakouts',
            'trigger' => 'post.action',
            'visibility' => 'public',
            'symptomActivationRequirements' => [
                'random_16',
            ],
            'tagConstraints' => [ActionEnum::MOVE => ModifierRequirementEnum::ANY_TAGS],
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
                'random_40',
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
                'reason_dirty',
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
