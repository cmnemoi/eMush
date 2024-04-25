<?php

namespace Mush\Game\Enum;

use Mush\RoomLog\Enum\LogDeclinationEnum;

/**
 * String needed for the translation service.
 */
class LanguageEnum
{
    // languages
    public const FRENCH = 'fr';
    public const ENGLISH = 'en';
    public const SPANISH = 'es';

    // translation parameters keys
    public const CHARACTER = 'character';
    public const TARGET_CHARACTER = 'target_character';
    public const END_CAUSE = 'end_cause';
    public const STATUS = 'status';
    public const EQUIPMENT = 'equipment';
    public const TARGET_EQUIPMENT = 'target_equipment';
    public const ITEM = 'item';
    public const TARGET_ITEM = 'target_item';
    public const DISEASE = 'disease';
    public const ROOMS = 'rooms';
    public const DISEASE_MESSAGE = 'disease_message';
    public const HUNTER = 'hunter';
    public const PLACE = 'place';
    public const ACTION = 'actions';
    public const ACTION_NAME = 'action_name';
    public const PROJECT = 'project';
    public const TARGET_PROJECT = 'target_project';

    // translation domains
    public const CHARACTERS = 'characters';
    public const ITEMS = 'items';
    public const EQUIPMENTS = 'equipments';

    public const COPROLALIA_PARAMETERS = [
        LogDeclinationEnum::BALLS_COPROLALIA,
        LogDeclinationEnum::PREFIX_COPROLALIA,
        LogDeclinationEnum::ADJECTIVE_COPROLALIA,
        LogDeclinationEnum::ANIMAL_COPROLALIA,
        LogDeclinationEnum::WORD_COPROLALIA,
    ];

    public const PARAMETER_KEY_TO_DOMAIN = [
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
        LogDeclinationEnum::BALLS_COPROLALIA => self::DISEASE_MESSAGE,
        LogDeclinationEnum::PREFIX_COPROLALIA => self::DISEASE_MESSAGE,
        LogDeclinationEnum::ADJECTIVE_COPROLALIA => self::DISEASE_MESSAGE,
        LogDeclinationEnum::ANIMAL_COPROLALIA => self::DISEASE_MESSAGE,
        LogDeclinationEnum::WORD_COPROLALIA => self::DISEASE_MESSAGE,
    ];

    public const TRANSLATE_PARAMETERS = [
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
        switch ($key) {
            case self::ITEM:
                return self::EQUIPMENT;

            case self::TARGET_ITEM:
                return self::TARGET_EQUIPMENT;

            default:
                return $key;
        }
    }
}
