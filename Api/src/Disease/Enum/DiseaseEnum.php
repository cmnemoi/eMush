<?php

namespace Mush\Disease\Enum;

enum DiseaseEnum: string
{
    case ACID_REFLUX = 'acid_reflux';
    case BLACK_BITE = 'black_bite';
    case CAT_ALLERGY = 'cat_allergy';
    case COLD = 'cold';
    case EXTREME_TINNITUS = 'extreme_tinnitus';
    case FLU = 'flu';
    case FOOD_POISONING = 'food_poisoning';
    case FUNGIC_INFECTION = 'fungic_infection';
    case GASTROENTERIS = 'gastroenteritis';
    case JUNKBUMPKINITIS = 'junkbumpkinitis';
    case MIGRAINE = 'migraine';
    case MUSH_ALLERGY = 'mush_allergy';
    case QUINCKS_OEDEMA = 'quincks_oedema';
    case REJUVENATION = 'rejuvenation';
    case RUBELLA = 'rubella';
    case SEPSIS = 'sepsis';
    case SINUS_STORM = 'sinus_storm';
    case SKIN_INFLAMMATION = 'skin_inflammation';
    case SLIGHT_NAUSEA = 'slight_nausea';
    case SMALLPOX = 'smallpox';
    case SPACE_RABIES = 'space_rabies';
    case SYPHILIS = 'syphilis';
    case TAPEWORM = 'tapeworm';
    case VITAMIN_DEFICIENCY = 'vitamin_deficiency';

    public function toConfigKey(string $configKey): string
    {
        return $this->value . '_' . $configKey;
    }

    public function toString(): string
    {
        return $this->value;
    }
}
