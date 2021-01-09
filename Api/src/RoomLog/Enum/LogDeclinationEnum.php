<?php

namespace Mush\RoomLog\Enum;

use Mush\Action\Enum\ActionEnum;

class LogDeclinationEnum
{
    public const REPAIR_FAIL_P1 = 'repair_fail_part1';
    public const REPAIR_FAIL_P2 = 'repair_fail_part2';

    public static function getAllParts(): array
    {
        return [
            ActionLogEnum::REPAIR_SUCCESS => [self::REPAIR_FAIL_P1, self::REPAIR_FAIL_P2],
        ];
    }

    public static function getAllVersions(): array
    {
        return [
            ActionLogEnum::CONSUME_SUCCESS => [
                'consume_success_1' => 1, //$hero a dévoré sa ration.
                'consume_success_2_1_1' => 1, //$hero s'est bien calé l'estomac.
                'consume_success_2_1_2' => 1,
                'consume_success_2_1_3' => 1,
                'consume_success_2_2_1' => 1, //$hero s'est bien calé la panse.
                'consume_success_2_2_2' => 1,
                'consume_success_2_2_3' => 1,
                'consume_success_2_3_1' => 1, //$hero s'est bien calé le ventre.
                'consume_success_2_3_2' => 1,
                'consume_success_2_3_3' => 1,
                'consume_success_2_4_1' => 1, //$hero s'est bien callé avec sa petite collation.
                'consume_success_2_4_2' => 1,
                'consume_success_2_4_3' => 1,
                'consume_success_2_5_1' => 1, //$hero s'est bien callé avec son bon petit repas.
                'consume_success_2_5_2' => 1,
                'consume_success_2_5_3' => 1,
                'consume_success_2_6_1' => 1, //$hero s'est bien callé avec son gros repas.
                'consume_success_2_6_2' => 1,
                'consume_success_2_6_3' => 1,
                'consume_success_2_7_1' => 1, //$hero s'est bien callé avec son petit repas.
                'consume_success_2_7_2' => 1,
                'consume_success_2_7_3' => 1,
                'consume_success_3_1_1' => 1, //$hero s'est callé avec sa petite collation.
                'consume_success_3_1_2' => 1,
                'consume_success_3_1_3' => 1,
                'consume_success_3_2_1' => 1, //$hero s'est callé avec son bon petit repas.
                'consume_success_3_2_2' => 1,
                'consume_success_3_2_3' => 1,
                'consume_success_3_3_1' => 1, //$hero s'est callé avec son gros repas.
                'consume_success_3_3_2' => 1,
                'consume_success_3_3_3' => 1,
                'consume_success_3_4_1' => 1, //$hero s'est callé avec son petit repas.
                'consume_success_3_4_2' => 1,
                'consume_success_3_4_3' => 1,
                'consume_success_4_1_1' => 1, //$hero s'est mangé sa petite collation.
                'consume_success_4_1_2' => 1,
                'consume_success_4_1_3' => 1,
                'consume_success_4_2_1' => 1, //$hero s'est mangé son bon petit repas.
                'consume_success_4_2_2' => 1,
                'consume_success_4_2_3' => 1,
                'consume_success_4_3_1' => 1, //$hero s'est mangé son gros repas.
                'consume_success_4_3_2' => 1,
                'consume_success_4_3_3' => 1,
                'consume_success_4_4_1' => 1, //$hero s'est mangé son petit repas.
                'consume_success_4_4_2' => 1,
                'consume_success_4_4_3' => 1,
            ],
            ActionEnum::SHRED => [
                'shred_1' => 1,
                'shred_2' => 1,
                'shred_3' => 1,
                'shred_4' => 1,
            ],
            ActionEnum::RETRIEVE_OXYGEN => [
                'retrieve_oxygen_v1' => 1,
                'retrieve_oxygen_v2' => 1,
                'retrieve_oxygen_v3' => 1,
                'retrieve_oxygen_v4' => 1,
                'retrieve_oxygen_v5' => 1,
                'retrieve_oxygen_v6' => 1,
                'retrieve_oxygen_v7' => 1,
                'retrieve_oxygen_v8' => 1,
                'retrieve_oxygen_v9' => 1,
                'retrieve_oxygen_v10' => 1,
            ],
            self::REPAIR_FAIL_P1 => [
                'repair_fail_p1_v1' => 1,
                'repair_fail_p1_v2' => 1,
                'repair_fail_p1_v3' => 1,
                'repair_fail_p1_v4' => 1,
                'repair_fail_p1_v5' => 1,
                'repair_fail_p1_v6' => 1,
                'repair_fail_p1_v7' => 1,
                'repair_fail_p1_v8' => 1,
                'repair_fail_p1_v9' => 1,
                'repair_fail_p1_v10' => 1,
            ],
            self::REPAIR_FAIL_P2 => [
                'repair_fail_p2_v1' => 1,
                'repair_fail_p2_v2' => 1,
                'repair_fail_p2_v3' => 1,
                'repair_fail_p2_v4' => 1,
                'repair_fail_p2_v5' => 1,
                'repair_fail_p2_v6' => 1,
                'repair_fail_p2_v7' => 1,
                'repair_fail_p2_v8' => 1,
                'repair_fail_p2_v9' => 1,
                'repair_fail_p2_v10' => 1,
                'repair_fail_p2_v11' => 1,
                'repair_fail_p2_v12' => 1,
                'repair_fail_p2_v13' => 1,
                'repair_fail_p2_v14' => 1,
                'repair_fail_p2_v15' => 1,
                'repair_fail_p2_v16' => 1,
                'repair_fail_p2_v17' => 1,
                'repair_fail_p2_v18' => 1,
                'repair_fail_p2_v19' => 1,
                'repair_fail_p2_v20' => 1,
                'repair_fail_p2_v21' => 1,
                'repair_fail_p2_v22' => 1,
                'repair_fail_p2_v23' => 1,
                'repair_fail_p2_v24' => 1,
                'repair_fail_p2_v25' => 1,
                'repair_fail_p2_v26' => 1,
                'repair_fail_p2_v27' => 1,
                'repair_fail_p2_v28' => 1,
                'repair_fail_p2_v29' => 1,
                'repair_fail_p2_v30' => 1,
                'repair_fail_p2_v31' => 1,
                'repair_fail_p2_v32' => 1,
                'repair_fail_p2_v33' => 1,
                'repair_fail_p2_v34' => 1,
                'repair_fail_p2_v35' => 1,
                'repair_fail_p2_v36' => 1,
                'repair_fail_p2_v37' => 1,
                'repair_fail_p2_v38' => 1,
                'repair_fail_p2_v39' => 1,
                'repair_fail_p2_v40' => 1,
                'repair_fail_p2_v41' => 1,
                'repair_fail_p2_v42' => 1,
                'repair_fail_p2_v43' => 1,
                'repair_fail_p2_v44' => 1,
                'repair_fail_p2_v45' => 1,
                'repair_fail_p2_v46' => 1,
                'repair_fail_p2_v47' => 1,
            ],
        ];
    }

    public static function getDeclination(string $key): ?array
    {
        return self::getAllVersions()[$key] ?? null;
    }

    public static function getComposed(string $key): ?array
    {
        return self::getAllParts()[$key] ?? null;
    }
}
