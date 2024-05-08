<?php

namespace Mush\Game\Enum;

use Mush\RoomLog\Enum\LogDeclinationEnum;

/**
 * String needed for the translation service.
 */
abstract class LanguageEnum
{
    // languages
    public const string FRENCH = 'fr';
    public const string ENGLISH = 'en';
    public const string SPANISH = 'es';

    // translation parameters keys
    public const string CHARACTER = 'character';
    public const string TARGET_CHARACTER = 'target_character';
    public const string END_CAUSE = 'end_cause';
    public const string STATUS = 'status';
    public const string EQUIPMENT = 'equipment';
    public const string TARGET_EQUIPMENT = 'target_equipment';
    public const string ITEM = 'item';
    public const string TARGET_ITEM = 'target_item';
    public const string DISEASE = 'disease';
    public const string ROOMS = 'rooms';
    public const string DISEASE_MESSAGE = 'disease_message';
    public const string HUNTER = 'hunter';
    public const string PLACE = 'place';
    public const string ACTION = 'actions';
    public const string ACTION_NAME = 'action_name';
    public const string PROJECT = 'project';
    public const string TARGET_PROJECT = 'target_project';
    public const string DRONE = 'drone';
    public const string TARGET_DRONE = 'target_drone';

    // translation domains
    public const string CHARACTERS = 'characters';
    public const string ITEMS = 'items';
    public const string EQUIPMENTS = 'equipments';

    public const array COPROLALIA_PARAMETERS = [
        LogDeclinationEnum::BALLS_COPROLALIA,
        LogDeclinationEnum::PREFIX_COPROLALIA,
        LogDeclinationEnum::ADJECTIVE_COPROLALIA,
        LogDeclinationEnum::ANIMAL_COPROLALIA,
        LogDeclinationEnum::WORD_COPROLALIA,
    ];

    public const array PARAMETER_KEY_TO_DOMAIN = [
        self::TARGET_EQUIPMENT => self::EQUIPMENTS,
        self::EQUIPMENT => self::EQUIPMENTS,
        self::TARGET_ITEM => self::ITEMS,
        self::ITEM => self::ITEMS,
        self::CHARACTER => self::CHARACTERS,
        self::TARGET_CHARACTER => self::CHARACTERS,
        self::END_CAUSE => self::END_CAUSE,
        self::STATUS => self::STATUS,
        self::ROOMS => self::ROOMS,
        self::DISEASE => self::DISEASE,
        self::HUNTER => self::HUNTER,
        self::PLACE => self::ROOMS,
        self::ACTION_NAME => self::ACTION,
        self::PROJECT => self::PROJECT,
        self::TARGET_PROJECT => self::PROJECT,
        self::DRONE => self::ITEMS,
        self::TARGET_DRONE => self::ITEMS,
        LogDeclinationEnum::BALLS_COPROLALIA => self::DISEASE_MESSAGE,
        LogDeclinationEnum::PREFIX_COPROLALIA => self::DISEASE_MESSAGE,
        LogDeclinationEnum::ADJECTIVE_COPROLALIA => self::DISEASE_MESSAGE,
        LogDeclinationEnum::ANIMAL_COPROLALIA => self::DISEASE_MESSAGE,
        LogDeclinationEnum::WORD_COPROLALIA => self::DISEASE_MESSAGE,
    ];

    public const array TRANSLATE_PARAMETERS = [
        self::FRENCH => [
            self::EQUIPMENT => ['short_name', 'gender', 'first_letter', 'plural_name'],
            self::TARGET_EQUIPMENT => ['short_name', 'gender', 'first_letter', 'plural_name'],
            self::ROOMS => ['loc_prep', 'name'],
            self::CHARACTER => ['name'],
            self::TARGET_CHARACTER => ['name'],
            self::END_CAUSE => ['name'],
            self::STATUS => ['name'],
            self::DISEASE => ['name'],
            self::HUNTER => ['name'],
            self::ACTION => ['name'],
            self::PROJECT => ['name'],
            LogDeclinationEnum::BALLS_COPROLALIA => ['balls_coprolalia'],
            LogDeclinationEnum::PREFIX_COPROLALIA => ['prefix_coprolalia'],
            LogDeclinationEnum::ADJECTIVE_COPROLALIA => [
                'adjective_male_single_coprolalia',
                'adjective_male_plural_coprolalia',
                'adjective_female_single_coprolalia',
                'adjective_female_plural_coprolalia',
            ],
            LogDeclinationEnum::ANIMAL_COPROLALIA => [
                'animal_coprolalia',
                'animal_plural_coprolalia',
                'preposition_coprolalia',
            ],
            LogDeclinationEnum::WORD_COPROLALIA => [
                'word_coprolalia',
                'word_plural_coprolalia',
            ],
        ],
        self::ENGLISH => [
            self::EQUIPMENT => ['short_name', 'first_letter', 'plural_name'],
            self::TARGET_EQUIPMENT => ['short_name', 'first_letter', 'plural_name'],
            self::ROOMS => ['loc_prep', 'name'],
            self::CHARACTER => ['name'],
            self::TARGET_CHARACTER => ['name'],
            self::END_CAUSE => ['name'],
            self::STATUS => ['name'],
            self::DISEASE => ['name'],
            self::HUNTER => ['name'],
            self::ACTION => ['name'],
            self::PROJECT => ['name'],
            LogDeclinationEnum::BALLS_COPROLALIA => ['balls_coprolalia'],
            LogDeclinationEnum::PREFIX_COPROLALIA => ['prefix_coprolalia'],
            LogDeclinationEnum::ADJECTIVE_COPROLALIA => ['adjective_coprolalia'],
            LogDeclinationEnum::ANIMAL_COPROLALIA => [
                'animal_coprolalia',
                'animal_plural_coprolalia',
            ],
            LogDeclinationEnum::WORD_COPROLALIA => [
                'word_coprolalia',
                'word_plural_coprolalia',
            ],
        ],
    ];

    public static function convertParameterKeyToTranslationKey(string $key): string
    {
        return match ($key) {
            self::ITEM => self::EQUIPMENT,
            self::TARGET_ITEM => self::TARGET_EQUIPMENT,
            self::DRONE => self::EQUIPMENT,
            self::TARGET_DRONE => self::TARGET_EQUIPMENT,
            default => $key,
        };
    }
}
