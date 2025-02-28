<?php

namespace Mush\RoomLog\Enum;

use Mush\Action\Enum\ActionEnum;
use Mush\Chat\Enum\DiseaseMessagesEnum;
use Mush\Chat\Enum\NeronMessageEnum;
use Mush\Equipment\Enum\WeaponEventEnum;

abstract class LogDeclinationEnum
{
    public const string VERSION = 'version';
    public const string VERSION_PART_1 = 'versionPart1';
    public const string VERSION_PART_2 = 'versionPart2';
    public const string VERSION_CRAZY = 'version_crazy';
    public const string VERSION_UNINHIB = 'version_uninhib';
    public const string WORD_COPROLALIA = 'word';
    public const string ANIMAL_COPROLALIA = 'animal';
    public const string PREFIX_COPROLALIA = 'prefix';
    public const string ADJECTIVE_COPROLALIA = 'adjective';
    public const string BALLS_COPROLALIA = 'balls';
    public const string PARANOIA_VERSION_4 = 'paranoia_version_4';
    public const string PARANOIA_VERSION_6 = 'paranoia_version_6';

    public static function getVersionNumber(): array
    {
        return [
            ActionLogEnum::CONSUME_SUCCESS => [self::VERSION => 46],
            ActionLogEnum::REPAIR_SUCCESS => [self::VERSION => 7],
            ActionLogEnum::REPAIR_FAIL => [self::VERSION_PART_1 => 10, self::VERSION_PART_2 => 50],
            ActionLogEnum::MOTIVATIONAL_SPEECH => [self::VERSION => 19],
            ActionLogEnum::BORING_SPEECH => [self::VERSION => 3],
            ActionLogEnum::ATTACK_SUCCESS => [self::VERSION => 9],
            ActionLogEnum::ATTACK_FAIL => [self::VERSION => 5],
            ActionLogEnum::ATTACK_CRITICAL_SUCCESS => [self::VERSION => 9],
            ActionLogEnum::ATTACK_CRITICAL_FAIL => [self::VERSION => 2],
            ActionLogEnum::ATTACK_ONE_SHOT => [self::VERSION => 2],
            ActionLogEnum::SHRED_SUCCESS => [self::VERSION => 4],
            ActionLogEnum::THROW_GRENADE_SUCCESS => [self::VERSION => 2],
            ActionEnum::RETRIEVE_OXYGEN->value => [self::VERSION => 10],
            NeronMessageEnum::ASPHYXIA_DEATH => [self::VERSION_PART_1 => 3, self::VERSION_PART_2 => 9],
            NeronMessageEnum::BROKEN_EQUIPMENT => [self::VERSION => 5],
            NeronMessageEnum::HUNTER_ARRIVAL => [self::VERSION_PART_1 => 4, self::VERSION_PART_2 => 4],
            NeronMessageEnum::NEW_FIRE => [self::VERSION_PART_1 => 3, self::VERSION_PART_2 => 3],
            NeronMessageEnum::NEW_PROJECT => [self::VERSION_PART_2 => 7, self::VERSION_UNINHIB => 7, self::VERSION_CRAZY => 12],
            NeronMessageEnum::PLAYER_DEATH => [self::VERSION => 7],
            NeronMessageEnum::REBEL_SIGNAL => [self::VERSION => 5],
            NeronMessageEnum::REPORT_FIRE => [self::VERSION => 5],
            NeronMessageEnum::TITLE_ATTRIBUTION => [self::VERSION => 8],
            NeronMessageEnum::TRAVEL_ARRIVAL => [self::VERSION => 11],
            NeronMessageEnum::SHIELD_BREACH => [self::VERSION => 4],
            NeronMessageEnum::PATCHING_UP => [self::VERSION_PART_1 => 10, self::VERSION_PART_2 => 4],
            NeronMessageEnum::ANNIVERSARY_BEGIN => [self::VERSION_PART_1 => 5, self::VERSION_PART_2 => 4],
            NeronMessageEnum::HALLOWEEN_BEGIN => [self::VERSION_PART_1 => 5, self::VERSION_PART_2 => 4],
            LogEnum::SELF_SURGERY_SUCCESS => [self::VERSION => 2],
            LogEnum::SURGERY_SUCCESS => [self::VERSION => 2],
            LogEnum::ATTACKED_BY_HUNTER => [self::VERSION => 4],
            DiseaseMessagesEnum::REPLACE_COPROLALIA => [
                self::VERSION => 13,
                self::ANIMAL_COPROLALIA => 14,
                self::PREFIX_COPROLALIA => 4,
                self::ADJECTIVE_COPROLALIA => 6,
                self::BALLS_COPROLALIA => 9,
                self::WORD_COPROLALIA => 20,
            ],
            DiseaseMessagesEnum::PRE_COPROLALIA => [
                self::VERSION => 3,
                self::ANIMAL_COPROLALIA => 14,
                self::PREFIX_COPROLALIA => 4,
                self::ADJECTIVE_COPROLALIA => 6,
                self::BALLS_COPROLALIA => 9,
                self::WORD_COPROLALIA => 20,
            ],
            DiseaseMessagesEnum::POST_COPROLALIA => [
                self::VERSION => 3,
                self::BALLS_COPROLALIA => 9,
                self::PREFIX_COPROLALIA => 4,
                self::ADJECTIVE_COPROLALIA => 6,
                self::ANIMAL_COPROLALIA => 14,
                self::WORD_COPROLALIA => 20,
            ],
            DiseaseMessagesEnum::REPLACE_PARANOIA => [self::VERSION => 12, self::PARANOIA_VERSION_4 => 4, self::PARANOIA_VERSION_6 => 6],
            DiseaseMessagesEnum::ACCUSE_PARANOIA => [self::VERSION => 10, self::PARANOIA_VERSION_4 => 4, self::PARANOIA_VERSION_6 => 6],
            DiseaseMessagesEnum::PRE_PARANOIA => [self::VERSION => 4],
            LogEnum::DRONE_REPAIRED_EQUIPMENT => [self::VERSION => 7],
            ActionLogEnum::PARTICIPATE_SUCCESS => [self::VERSION_UNINHIB => 14, self::VERSION_CRAZY => 5],
            LogEnum::CAT_MEOW => [self::VERSION => 17],
            LogEnum::CAT_HISS => [self::VERSION => 17],
            ActionLogEnum::SHOOT_CAT_FAIL => [self::VERSION => 4],
            ActionLogEnum::SHOOT_CAT_CRITICAL_FAIL => [self::VERSION => 2],
            WeaponEventEnum::BLASTER_SUCCESSFUL_SHOT->value => [self::VERSION => 5],
            WeaponEventEnum::BLASTER_FAILED_SHOT->value => [self::VERSION => 100],
            WeaponEventEnum::NATAMY_RIFLE_SUCCESSFUL_SHOT->value => [self::VERSION => 5],
            WeaponEventEnum::NATAMY_RIFLE_TARGET_BROKEN_SHOULDER_TARGET_CRITICAL_HAEMORRHAGE_40_PERCENTS_TARGET_HAEMORRHAGE_40_PERCENTS->value => [self::VERSION => 2],
            WeaponEventEnum::NATAMY_RIFLE_FAILED_SHOT->value => [self::VERSION => 100],
            WeaponEventEnum::KNIFE_SUCCESSFUL_HIT_10_MINOR_HAEMORRHAGE->value => [self::VERSION => 4],
            WeaponEventEnum::KNIFE_INSTAGIB_BLED->value => [self::VERSION => 2],
            WeaponEventEnum::KNIFE_FAILED_HIT->value => [self::VERSION => 3],
            WeaponEventEnum::KNIFE_SHOOTER_DROP_WEAPON->value => [self::VERSION => 2],
            WeaponEventEnum::OLD_FAITHFUL_SUCCESSFUL_SHOT->value => [self::VERSION => 5],
            WeaponEventEnum::OLD_FAITHFUL_TARGET_BROKEN_SHOULDER_TARGET_CRITICAL_HAEMORRHAGE_40_PERCENTS_TARGET_HAEMORRHAGE_40_PERCENTS->value => [self::VERSION => 2],
            WeaponEventEnum::OLD_FAITHFUL_FAILED_SHOT->value => [self::VERSION => 100],
            WeaponEventEnum::LIZARO_JUNGLE_SUCCESSFUL_SHOT->value => [self::VERSION => 5],
            WeaponEventEnum::LIZARO_JUNGLE_TARGET_BROKEN_SHOULDER_TARGET_CRITICAL_HAEMORRHAGE_40_PERCENTS_TARGET_HAEMORRHAGE_40_PERCENTS->value => [self::VERSION => 2],
            WeaponEventEnum::LIZARO_JUNGLE_FAILED_SHOT->value => [self::VERSION => 100],
            WeaponEventEnum::BARE_HANDS_SUCCESSFUL_HIT->value => [self::VERSION => 3],
            WeaponEventEnum::BARE_HANDS_FAILED_HIT->value => [self::VERSION => 3],
            WeaponEventEnum::ROCKET_LAUNCHER_SUCCESSFUL_HIT_2_RANDOM_WOUNDS_4_ITEMS_6_SPLASH->value => [self::VERSION => 2],
            WeaponEventEnum::ROCKET_LAUNCHER_ROCKETTED->value => [self::VERSION => 3],
            WeaponEventEnum::ROCKET_LAUNCHER_CRITICAL_3_RANDOM_WOUNDS_2_ITEMS->value => [self::VERSION => 3],
        ];
    }
}
