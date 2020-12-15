<?php

namespace Mush\RoomLog\Enum;

use Mush\Action\Actions\Action;
use Mush\Action\Enum\ActionEnum;

class LogDeclinationEnum
{
    public static function getAll(): array
    {
        return [
            ActionEnum::CONSUME => [
                'consume_1' => 1, //$hero a dévoré sa ration.
                'consume_2_1_1' => 1, //$hero s'est bien calé l'estomac.
                'consume_2_1_2' => 1,
                'consume_2_1_3' => 1,
                'consume_2_2_1' => 1,//$hero s'est bien calé la panse.
                'consume_2_2_2' => 1,
                'consume_2_2_3' => 1,
                'consume_2_3_1' => 1,//$hero s'est bien calé le ventre.
                'consume_2_3_2' => 1,
                'consume_2_3_3' => 1,
                'consume_2_4_1' => 1, //$hero s'est bien callé avec sa petite collation.
                'consume_2_4_2' => 1,
                'consume_2_4_3' => 1,
                'consume_2_5_1' => 1,//$hero s'est bien callé avec son bon petit repas.
                'consume_2_5_2' => 1,
                'consume_2_5_3' => 1,
                'consume_2_6_1' => 1,//$hero s'est bien callé avec son gros repas.
                'consume_2_6_2' => 1,
                'consume_2_6_3' => 1,
                'consume_2_7_1' => 1,//$hero s'est bien callé avec son petit repas.
                'consume_2_7_2' => 1,
                'consume_2_7_3' => 1,
                'consume_3_1_1' => 1,//$hero s'est callé avec sa petite collation.
                'consume_3_1_2' => 1,
                'consume_3_1_3' => 1,
                'consume_3_2_1' => 1,//$hero s'est callé avec son bon petit repas.
                'consume_3_2_2' => 1,
                'consume_3_2_3' => 1,
                'consume_3_3_1' => 1,//$hero s'est callé avec son gros repas.
                'consume_3_3_2' => 1,
                'consume_3_3_3' => 1,
                'consume_3_4_1' => 1,//$hero s'est callé avec son petit repas.
                'consume_3_4_2' => 1,
                'consume_3_4_3' => 1,
                'consume_4_1_1' => 1,//$hero s'est mangé sa petite collation.
                'consume_4_1_2' => 1,
                'consume_4_1_3' => 1,
                'consume_4_2_1' => 1,//$hero s'est mangé son bon petit repas.
                'consume_4_2_2' => 1,
                'consume_4_2_3' => 1,
                'consume_4_3_1' => 1,//$hero s'est mangé son gros repas.
                'consume_4_3_2' => 1,
                'consume_4_3_3' => 1,
                'consume_4_4_1' => 1,//$hero s'est mangé son petit repas.
                'consume_4_4_2' => 1,
                'consume_4_4_3' => 1,
            ],
            ActionEnum::SHRED => [
                'shred_1' => 1,
                'shred_2' => 1,
                'shred_3' => 1,
                'shred_4' => 1,
            ],
            ActionEnum::RETRIEVE_OXYGEN => [
                'retrieve_oxygen_1' => 1,
                'retrieve_oxygen_2' => 1,
                'retrieve_oxygen_3' => 1,
                'retrieve_oxygen_4' => 1,
                'retrieve_oxygen_5' => 1,
                'retrieve_oxygen_6' => 1,
                'retrieve_oxygen_7' => 1,
                'retrieve_oxygen_8' => 1,
                'retrieve_oxygen_9' => 1,
                'retrieve_oxygen_10' => 1,
            ]
        ];
    }

    public static function getDeclination(string $key): ?array
    {
        return self::getAll()[$key] ?? null;
    }
}
