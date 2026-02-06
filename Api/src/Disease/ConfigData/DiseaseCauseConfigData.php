<?php

namespace Mush\Disease\ConfigData;

use Mush\Disease\Dto\DiseaseCauseConfigDto;
use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Enum\DisorderEnum;
use Mush\Disease\Enum\InjuryEnum;

/** @codeCoverageIgnore */
class DiseaseCauseConfigData
{
    /**
     * @return DiseaseCauseConfigDto[]
     */
    public static function getAll(): array
    {
        return [
            new DiseaseCauseConfigDto(
                DiseaseCauseEnum::DRUG_CURE . '_default', // @TODO: Currently unused, please plug drugs into me instead of consumable disease config data
                DiseaseCauseEnum::DRUG_CURE,
                [
                    DiseaseEnum::VITAMIN_DEFICIENCY->toString() => 40, // @TODO: should it even be a weight table, or should all of them be always represented every game through distributing them amidst the pills?
                    DiseaseEnum::SYPHILIS->toString() => 5,
                    DiseaseEnum::SKIN_INFLAMMATION->toString() => 10,
                    DiseaseEnum::GASTROENTERIS->toString() => 40,
                    DiseaseEnum::FLU->toString() => 20,
                    DiseaseEnum::SEPSIS->toString() => 3,
                    DiseaseEnum::COLD->toString() => 50,
                    DiseaseEnum::RUBELLA->toString() => 10,
                    DiseaseEnum::SINUS_STORM->toString() => 20,
                    DiseaseEnum::TAPEWORM->toString() => 50,
                    DisorderEnum::PARANOIA->toString() => 10,
                    DisorderEnum::DEPRESSION->toString() => 10,
                    DisorderEnum::CHRONIC_MIGRAINE->toString() => 10,
                ]
            ),
            new DiseaseCauseConfigDto(
                DiseaseCauseEnum::ALIEN_FRUIT_CAUSE . '_default', // @TODO: Currently unused, please plug fruits into me instead of consumable disease config data
                DiseaseCauseEnum::ALIEN_FRUIT_CAUSE,
                [
                    DiseaseEnum::SINUS_STORM->toString() => 20,
                    DiseaseEnum::MIGRAINE->toString() => 50,
                    DisorderEnum::CHRONIC_MIGRAINE->toString() => 10,
                    DiseaseEnum::EXTREME_TINNITUS->toString() => 10,
                    DiseaseEnum::ACID_REFLUX->toString() => 10,
                    DiseaseEnum::FLU->toString() => 20,
                    DiseaseEnum::RUBELLA->toString() => 10,
                    DiseaseEnum::GASTROENTERIS->toString() => 40,
                    DiseaseEnum::SMALLPOX->toString() => 5,
                    DiseaseEnum::BLACK_BITE->toString() => 10,
                    DiseaseEnum::REJUVENATION->toString() => 10,
                    DisorderEnum::PSYCHOTIC_EPISODE->toString() => 10,
                    DisorderEnum::AGORAPHOBIA->toString() => 5,
                    DiseaseEnum::CAT_ALLERGY->toString() => 2,
                    DisorderEnum::CRABISM->toString() => 10,
                    DisorderEnum::VERTIGO->toString() => 15,
                    DiseaseEnum::SKIN_INFLAMMATION->toString() => 10,
                    DiseaseEnum::SPACE_RABIES->toString() => 10,
                    DiseaseEnum::MUSH_ALLERGY->toString() => 2,
                    DisorderEnum::PARANOIA->toString() => 10,
                    DiseaseEnum::TAPEWORM->toString() => 50,
                    DiseaseEnum::FOOD_POISONING->toString() => 10,
                    DiseaseEnum::FUNGIC_INFECTION->toString() => 10,
                    DiseaseEnum::SLIGHT_NAUSEA->toString() => 5,
                ]
            ),
            new DiseaseCauseConfigDto(
                DiseaseCauseEnum::ALIEN_FRUIT_CURE . '_default', // @TODO: Currently unused, please plug fruits into me instead of consumable disease config data
                DiseaseCauseEnum::ALIEN_FRUIT_CURE,
                [
                    DiseaseEnum::COLD->toString() => 50,
                    DiseaseEnum::SINUS_STORM->toString() => 20,
                    DiseaseEnum::MIGRAINE->toString() => 50,
                    DiseaseEnum::VITAMIN_DEFICIENCY->toString() => 40,
                    DisorderEnum::CHRONIC_MIGRAINE->toString() => 10,
                    DiseaseEnum::EXTREME_TINNITUS->toString() => 10,
                    DiseaseEnum::ACID_REFLUX->toString() => 10,
                    DiseaseEnum::FLU->toString() => 20,
                    DiseaseEnum::RUBELLA->toString() => 10,
                    DiseaseEnum::GASTROENTERIS->toString() => 40,
                    DiseaseEnum::SYPHILIS->toString() => 5,
                    DiseaseEnum::BLACK_BITE->toString() => 10,
                    DiseaseEnum::REJUVENATION->toString() => 10,
                    DisorderEnum::DEPRESSION->toString() => 10,
                    DisorderEnum::PSYCHOTIC_EPISODE->toString() => 10,
                    DisorderEnum::AGORAPHOBIA->toString() => 5,
                    DiseaseEnum::CAT_ALLERGY->toString() => 2,
                    DisorderEnum::CRABISM->toString() => 10,
                    DisorderEnum::COPROLALIA->toString() => 10,
                    DisorderEnum::CHRONIC_VERTIGO->toString() => 5,
                    DisorderEnum::VERTIGO->toString() => 15,
                    DiseaseEnum::SKIN_INFLAMMATION->toString() => 10,
                    DiseaseEnum::SPACE_RABIES->toString() => 10,
                    DiseaseEnum::MUSH_ALLERGY->toString() => 2,
                    DiseaseEnum::TAPEWORM->toString() => 50,
                    DiseaseEnum::SEPSIS->toString() => 3,
                    DiseaseEnum::FOOD_POISONING->toString() => 10,
                    DiseaseEnum::FUNGIC_INFECTION->toString() => 10,
                    DiseaseEnum::SLIGHT_NAUSEA->toString() => 5,
                ]
            ),
            new DiseaseCauseConfigDto(
                DiseaseCauseEnum::PERISHED_FOOD . '_default',
                DiseaseCauseEnum::PERISHED_FOOD,
                [
                    DiseaseEnum::FOOD_POISONING->toString() => 10,
                ]
            ),
            new DiseaseCauseConfigDto(
                DiseaseCauseEnum::CYCLE_LOW_MORALE . '_default',
                DiseaseCauseEnum::CYCLE_LOW_MORALE,
                [
                    DiseaseEnum::VITAMIN_DEFICIENCY->toString() => 40,
                    DiseaseEnum::CAT_ALLERGY->toString() => 2,
                    DiseaseEnum::ACID_REFLUX->toString() => 10,
                    DiseaseEnum::SLIGHT_NAUSEA->toString() => 5,
                    DiseaseEnum::SKIN_INFLAMMATION->toString() => 10,
                    DiseaseEnum::FLU->toString() => 20,
                    DiseaseEnum::GASTROENTERIS->toString() => 40,
                    DiseaseEnum::MIGRAINE->toString() => 50,
                    DiseaseEnum::COLD->toString() => 50,
                    DiseaseEnum::SINUS_STORM->toString() => 20,
                    DisorderEnum::DEPRESSION->toString() => 100,
                ]
            ),
            new DiseaseCauseConfigDto(
                DiseaseCauseEnum::CYCLE . '_default',
                DiseaseCauseEnum::CYCLE,
                [
                    DiseaseEnum::VITAMIN_DEFICIENCY->toString() => 40,
                    DiseaseEnum::CAT_ALLERGY->toString() => 2,
                    DiseaseEnum::ACID_REFLUX->toString() => 10,
                    DiseaseEnum::SLIGHT_NAUSEA->toString() => 5,
                    DiseaseEnum::SKIN_INFLAMMATION->toString() => 10,
                    DiseaseEnum::FLU->toString() => 20,
                    DiseaseEnum::GASTROENTERIS->toString() => 40,
                    DiseaseEnum::MIGRAINE->toString() => 50,
                    DiseaseEnum::COLD->toString() => 50,
                    DiseaseEnum::SINUS_STORM->toString() => 20,
                    DisorderEnum::DEPRESSION->toString() => 10,
                ]
            ),
            new DiseaseCauseConfigDto(
                DiseaseCauseEnum::MAKE_SICK . '_default',
                DiseaseCauseEnum::MAKE_SICK,
                [
                    DiseaseEnum::FLU->toString() => 20,
                    DiseaseEnum::COLD->toString() => 50,
                    DiseaseEnum::FUNGIC_INFECTION->toString() => 10,
                    DiseaseEnum::EXTREME_TINNITUS->toString() => 10,
                ]
            ),
            new DiseaseCauseConfigDto(
                DiseaseCauseEnum::SURGERY . '_default',
                DiseaseCauseEnum::SURGERY,
                [
                    DiseaseEnum::SEPSIS->toString() => 1,
                ]
            ),
            new DiseaseCauseConfigDto(
                DiseaseCauseEnum::CAT_ALLERGY . '_default', // @TODO: This should be oedema not cat_allergy
                DiseaseCauseEnum::CAT_ALLERGY,
                [
                    InjuryEnum::BURNT_HAND->toString() => 5,
                    InjuryEnum::BURNT_ARMS->toString() => 5,
                ]
            ),
            new DiseaseCauseConfigDto(
                DiseaseCauseEnum::INFECTION . '_default',
                DiseaseCauseEnum::INFECTION,
                [
                    DiseaseEnum::FLU->toString() => 50,
                    DiseaseEnum::FUNGIC_INFECTION->toString() => 10,
                    DiseaseEnum::MUSH_ALLERGY->toString() => 2,
                    DisorderEnum::VERTIGO->toString() => 15,
                ]
            ),
            new DiseaseCauseConfigDto(
                DiseaseCauseEnum::SEX . '_default',
                DiseaseCauseEnum::SEX,
                [
                    DiseaseEnum::SKIN_INFLAMMATION->toString() => 10,
                    DiseaseEnum::FLU->toString() => 20,
                    DiseaseEnum::GASTROENTERIS->toString() => 40,
                    DiseaseEnum::FUNGIC_INFECTION->toString() => 10,
                    DiseaseEnum::SYPHILIS->toString() => 5,
                ]
            ),
            new DiseaseCauseConfigDto(
                DiseaseCauseEnum::TRAUMA . '_default',
                DiseaseCauseEnum::TRAUMA,
                [
                    DiseaseEnum::MIGRAINE->toString() => 50,
                    DisorderEnum::CHRONIC_MIGRAINE->toString() => 10,
                    DiseaseEnum::GASTROENTERIS->toString() => 40,
                    DisorderEnum::DEPRESSION->toString() => 10,
                    DisorderEnum::PSYCHOTIC_EPISODE->toString() => 10,
                    DisorderEnum::AGORAPHOBIA->toString() => 5,
                    DisorderEnum::CRABISM->toString() => 10,
                    DisorderEnum::COPROLALIA->toString() => 10,
                    DisorderEnum::CHRONIC_VERTIGO->toString() => 5,
                    DisorderEnum::WEAPON_PHOBIA->toString() => 10,
                    DisorderEnum::PARANOIA->toString() => 10,
                    DisorderEnum::SPLEEN->toString() => 2,
                ]
            ),
            new DiseaseCauseConfigDto(
                DiseaseCauseEnum::CONTACT . '_default',
                DiseaseCauseEnum::CONTACT,
                [
                    DiseaseEnum::COLD->toString() => 50,
                    DiseaseEnum::MIGRAINE->toString() => 50,
                    DiseaseEnum::FLU->toString() => 20,
                    DiseaseEnum::RUBELLA->toString() => 10,
                    DiseaseEnum::GASTROENTERIS->toString() => 40,
                ]
            ),
            new DiseaseCauseConfigDto(
                DiseaseCauseEnum::ALIEN_FIGHT . '_default',
                DiseaseCauseEnum::ALIEN_FIGHT,
                [
                    DiseaseEnum::FLU->toString() => 20,
                    DiseaseEnum::SYPHILIS->toString() => 5,
                    DiseaseEnum::BLACK_BITE->toString() => 10,
                    DiseaseEnum::REJUVENATION->toString() => 10,
                    DisorderEnum::AILUROPHOBIA->toString() => 2,
                    DiseaseEnum::SPACE_RABIES->toString() => 10,
                    DiseaseEnum::SEPSIS->toString() => 3,
                ]
            ),
            new DiseaseCauseConfigDto(
                DiseaseCauseEnum::EXPLORATION . '_default',
                DiseaseCauseEnum::EXPLORATION,
                [
                    DiseaseEnum::MIGRAINE->toString() => 50,
                    DiseaseEnum::ACID_REFLUX->toString() => 10,
                    DiseaseEnum::FLU->toString() => 20,
                    DiseaseEnum::RUBELLA->toString() => 10,
                    DiseaseEnum::GASTROENTERIS->toString() => 40,
                    DiseaseEnum::SMALLPOX->toString() => 5,
                    DiseaseEnum::BLACK_BITE->toString() => 10,
                    DiseaseEnum::REJUVENATION->toString() => 10,
                    DiseaseEnum::SKIN_INFLAMMATION->toString() => 10,
                    DiseaseEnum::SLIGHT_NAUSEA->toString() => 5,
                ]
            ),
            new DiseaseCauseConfigDto(
                DiseaseCauseEnum::SPACE_TRAVEL . '_default', // @TODO: currently unused, please plug me into the relevant cause
                DiseaseCauseEnum::SPACE_TRAVEL,
                [
                    DiseaseEnum::COLD->toString() => 50,
                    DiseaseEnum::SINUS_STORM->toString() => 20,
                    DiseaseEnum::MIGRAINE->toString() => 50,
                    DisorderEnum::CHRONIC_MIGRAINE->toString() => 10,
                    DiseaseEnum::EXTREME_TINNITUS->toString() => 10,
                    DisorderEnum::CHRONIC_VERTIGO->toString() => 5,
                    DisorderEnum::VERTIGO->toString() => 15,
                ]
            ),
        ];
    }

    public static function getByName(string $name): DiseaseCauseConfigDto
    {
        $data = current(array_filter(self::getAll(), static fn (DiseaseCauseConfigDto $data) => $data->name === $name));

        if (!$data) {
            throw new \Exception(\sprintf('Disease cause config %s not found', $name));
        }

        return $data;
    }
}
