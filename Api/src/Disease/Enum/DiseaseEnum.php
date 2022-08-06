<?php

namespace Mush\Disease\Enum;

class DiseaseEnum
{
    public const ACID_REFLUX = 'acid_reflux';
    public const BLACK_BITE = 'black_bite';
    public const CAT_ALLERGY = 'cat_allergy';
    public const COLD = 'cold';
    public const EXTREME_TINNITUS = 'extreme_tinnitus';
    public const FLU = 'flu';
    public const FOOD_POISONING = 'food_poisoning';
    public const FUNGIC_INFECTION = 'fungic_infection';
    public const GASTROENTERIS = 'gastroenteritis';
    public const JUNKBUMPKINITIS = 'junkbumpkinitis';
    public const MIGRAINE = 'migraine';
    public const MUSH_ALLERGY = 'mush_allergy';
    public const QUINCKS_OEDEMA = 'quincks_oedema';
    public const REJUVENATION = 'rejuvenation';
    public const RUBELLA = 'rubella';
    public const SEPSIS = 'sepsis';
    public const SINUS_STORM = 'sinus_storm';
    public const SKIN_INFLAMMATION = 'skin_inflammation';
    public const SLIGHT_NAUSEA = 'slight_nausea';
    public const SMALLPOX = 'smallpox';
    public const SPACE_RABIES = 'space_rabies';
    public const SYPHILIS = 'syphilis';
    public const TAPEWORM = 'tapeworm';
    public const VITAMIN_DEFICIENCY = 'vitamin_deficiency';

    public static function getAllDiseases(): array
    {
        return [
            self::ACID_REFLUX,
            self::BLACK_BITE,
            self::CAT_ALLERGY,
            self::COLD,
            self::EXTREME_TINNITUS,
            self::FLU,
            self::FOOD_POISONING,
            self::FUNGIC_INFECTION,
            self::GASTROENTERIS,
            self::JUNKBUMPKINITIS,
            self::MIGRAINE,
            self::MUSH_ALLERGY,
            self::QUINCKS_OEDEMA,
            self::REJUVENATION,
            self::RUBELLA,
            self::SEPSIS,
            self::SINUS_STORM,
            self::SKIN_INFLAMMATION,
            self::SLIGHT_NAUSEA,
            self::SMALLPOX,
            self::SPACE_RABIES,
            self::SYPHILIS,
            self::TAPEWORM,
            self::VITAMIN_DEFICIENCY,
        ];
    }

    public static function getBacterialContactDiseases(): array
    {
        return [
            self::COLD,
            self::FUNGIC_INFECTION,
            self::FLU,
            self::EXTREME_TINNITUS,
        ];
    }

    public static function getCycleDiseases(): array
    {
        return [
            self::MUSH_ALLERGY,
            self::CAT_ALLERGY,
            self::FUNGIC_INFECTION,
            self::SINUS_STORM,
            self::VITAMIN_DEFICIENCY,
            self::ACID_REFLUX,
            self::MIGRAINE,
            self::GASTROENTERIS,
            self::COLD,
            self::SLIGHT_NAUSEA,
        ];
    }

    public static function getCycleDepressedDiseases(): array
    {
        return [
            self::MUSH_ALLERGY,
            self::CAT_ALLERGY,
            self::FUNGIC_INFECTION,
            self::SINUS_STORM,
            self::VITAMIN_DEFICIENCY,
            self::ACID_REFLUX,
            self::MIGRAINE,
            self::GASTROENTERIS,
            self::COLD,
            self::SLIGHT_NAUSEA,
        ];
    }

    public static function getFakeDiseases(): array
    {
        return [
            self::COLD,
            self::EXTREME_TINNITUS,
            self::CAT_ALLERGY,
            self::SINUS_STORM,
        ];
    }

    public static function getPerishedFoodDiseases(): array
    {
        return [self::FOOD_POISONING];
    }
}
