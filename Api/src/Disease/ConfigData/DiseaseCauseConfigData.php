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
                DiseaseCauseEnum::ALIEN_FRUIT . '_default',
                DiseaseCauseEnum::ALIEN_FRUIT,
                [
                    DiseaseEnum::CAT_ALLERGY->toString() => 1,
                    DiseaseEnum::MUSH_ALLERGY->toString() => 1,
                    DiseaseEnum::SEPSIS->toString() => 1,
                    DiseaseEnum::SLIGHT_NAUSEA->toString() => 1,
                    DiseaseEnum::SMALLPOX->toString() => 1,
                    DiseaseEnum::SYPHILIS->toString() => 1,
                    DisorderEnum::AILUROPHOBIA->toString() => 1,
                    DisorderEnum::COPROLALIA->toString() => 1,
                    DisorderEnum::SPLEEN->toString() => 1,
                    DisorderEnum::WEAPON_PHOBIA->toString() => 1,
                    DisorderEnum::CHRONIC_VERTIGO->toString() => 1,
                    DisorderEnum::PARANOIA->toString() => 1,
                    DiseaseEnum::ACID_REFLUX->toString() => 2,
                    DiseaseEnum::SKIN_INFLAMMATION->toString() => 2,
                    DisorderEnum::AGORAPHOBIA->toString() => 2,
                    DisorderEnum::CHRONIC_MIGRAINE->toString() => 2,
                    DisorderEnum::VERTIGO->toString() => 2,
                    DisorderEnum::DEPRESSION->toString() => 2,
                    DisorderEnum::PSYCHOTIC_EPISODE->toString() => 2,
                    DisorderEnum::CRABISM->toString() => 4,
                    DiseaseEnum::BLACK_BITE->toString() => 4,
                    DiseaseEnum::COLD->toString() => 4,
                    DiseaseEnum::EXTREME_TINNITUS->toString() => 4,
                    DiseaseEnum::FOOD_POISONING->toString() => 4,
                    DiseaseEnum::FUNGIC_INFECTION->toString() => 4,
                    DiseaseEnum::REJUVENATION->toString() => 4,
                    DiseaseEnum::RUBELLA->toString() => 4,
                    DiseaseEnum::SINUS_STORM->toString() => 4,
                    DiseaseEnum::SPACE_RABIES->toString() => 4,
                    DiseaseEnum::VITAMIN_DEFICIENCY->toString() => 4,
                    DiseaseEnum::FLU->toString() => 8,
                    DiseaseEnum::GASTROENTERIS->toString() => 8,
                    DiseaseEnum::MIGRAINE->toString() => 8,
                    DiseaseEnum::TAPEWORM->toString() => 8,
                ]
            ),
            new DiseaseCauseConfigDto(
                DiseaseCauseEnum::PERISHED_FOOD . '_default',
                DiseaseCauseEnum::PERISHED_FOOD,
                [
                    DiseaseEnum::FOOD_POISONING->toString() => 1,
                ]
            ),
            new DiseaseCauseConfigDto(
                DiseaseCauseEnum::CYCLE_LOW_MORALE . '_default',
                DiseaseCauseEnum::CYCLE_LOW_MORALE,
                [
                    DiseaseEnum::MUSH_ALLERGY->toString() => 1,
                    DiseaseEnum::CAT_ALLERGY->toString() => 1,
                    DiseaseEnum::FUNGIC_INFECTION->toString() => 2,
                    DiseaseEnum::SINUS_STORM->toString() => 2,
                    DiseaseEnum::VITAMIN_DEFICIENCY->toString() => 4,
                    DiseaseEnum::ACID_REFLUX->toString() => 4,
                    DiseaseEnum::MIGRAINE->toString() => 4,
                    DiseaseEnum::GASTROENTERIS->toString() => 8,
                    DiseaseEnum::COLD->toString() => 8,
                    DiseaseEnum::SLIGHT_NAUSEA->toString() => 8,
                    DisorderEnum::DEPRESSION->toString() => 32,
                ]
            ),
            new DiseaseCauseConfigDto(
                DiseaseCauseEnum::CYCLE . '_default',
                DiseaseCauseEnum::CYCLE,
                [
                    DiseaseEnum::MUSH_ALLERGY->toString() => 1,
                    DiseaseEnum::CAT_ALLERGY->toString() => 1,
                    DiseaseEnum::FUNGIC_INFECTION->toString() => 2,
                    DiseaseEnum::SINUS_STORM->toString() => 2,
                    DiseaseEnum::VITAMIN_DEFICIENCY->toString() => 4,
                    DiseaseEnum::ACID_REFLUX->toString() => 4,
                    DiseaseEnum::MIGRAINE->toString() => 4,
                    DiseaseEnum::GASTROENTERIS->toString() => 8,
                    DiseaseEnum::COLD->toString() => 8,
                    DiseaseEnum::SLIGHT_NAUSEA->toString() => 8,
                ]
            ),
            new DiseaseCauseConfigDto(
                DiseaseCauseEnum::MAKE_SICK . '_default',
                DiseaseCauseEnum::MAKE_SICK,
                [
                    DiseaseEnum::COLD->toString() => 1,
                    DiseaseEnum::FUNGIC_INFECTION->toString() => 1,
                    DiseaseEnum::FLU->toString() => 1,
                    DiseaseEnum::EXTREME_TINNITUS->toString() => 1,
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
                DiseaseCauseEnum::CAT_ALLERGY . '_default',
                DiseaseCauseEnum::CAT_ALLERGY,
                [
                    InjuryEnum::BURNT_HAND->toString() => 1,
                    InjuryEnum::BURNT_ARMS->toString() => 1,
                ]
            ),
            new DiseaseCauseConfigDto(
                DiseaseCauseEnum::INFECTION . '_default',
                DiseaseCauseEnum::INFECTION,
                [
                    DiseaseEnum::FLU->toString() => 50,
                    DiseaseEnum::GASTROENTERIS->toString() => 20,
                    DiseaseEnum::FUNGIC_INFECTION->toString() => 15,
                    DiseaseEnum::MIGRAINE->toString() => 10,
                    DiseaseEnum::MUSH_ALLERGY->toString() => 5,
                ]
            ),
            new DiseaseCauseConfigDto(
                DiseaseCauseEnum::SEX . '_default',
                DiseaseCauseEnum::SEX,
                [
                    DiseaseEnum::FLU->toString() => 1,
                    DiseaseEnum::GASTROENTERIS->toString() => 1,
                    DiseaseEnum::SKIN_INFLAMMATION->toString() => 1,
                ]
            ),
            new DiseaseCauseConfigDto(
                DiseaseCauseEnum::TRAUMA . '_default',
                DiseaseCauseEnum::TRAUMA,
                [
                    DiseaseEnum::MIGRAINE->toString() => 30,
                    DiseaseEnum::GASTROENTERIS->toString() => 30,
                    DisorderEnum::CHRONIC_MIGRAINE->toString() => 6,
                    DisorderEnum::PSYCHOTIC_EPISODE->toString() => 6,
                    DisorderEnum::WEAPON_PHOBIA->toString() => 6,
                    DisorderEnum::PARANOIA->toString() => 6,
                    DisorderEnum::CRABISM->toString() => 6,
                    DisorderEnum::COPROLALIA->toString() => 6,
                    DisorderEnum::DEPRESSION->toString() => 6,
                    DisorderEnum::AGORAPHOBIA->toString() => 3,
                    DisorderEnum::CHRONIC_VERTIGO->toString() => 3,
                    DisorderEnum::SPLEEN->toString() => 1,
                ]
            ),
            new DiseaseCauseConfigDto(
                DiseaseCauseEnum::CONTACT . '_default',
                DiseaseCauseEnum::CONTACT,
                [
                    DiseaseEnum::FLU->toString() => 1,
                    DiseaseEnum::GASTROENTERIS->toString() => 1,
                    DiseaseEnum::SKIN_INFLAMMATION->toString() => 1,
                ]
            ),
            new DiseaseCauseConfigDto(
                DiseaseCauseEnum::ALIEN_FIGHT . '_default',
                DiseaseCauseEnum::ALIEN_FIGHT,
                [
                    DiseaseEnum::FLU->toString() => 1,
                    DiseaseEnum::SYPHILIS->toString() => 1,
                    DiseaseEnum::BLACK_BITE->toString() => 1,
                    DiseaseEnum::REJUVENATION->toString() => 1,
                    DiseaseEnum::SPACE_RABIES->toString() => 1,
                    DiseaseEnum::SEPSIS->toString() => 1,
                    DisorderEnum::AILUROPHOBIA->toString() => 1,
                ]
            ),
            new DiseaseCauseConfigDto(
                DiseaseCauseEnum::EXPLORATION . '_default',
                DiseaseCauseEnum::EXPLORATION,
                [
                    DiseaseEnum::MIGRAINE->toString() => 1,
                    DiseaseEnum::ACID_REFLUX->toString() => 1,
                    DiseaseEnum::FLU->toString() => 1,
                    DiseaseEnum::RUBELLA->toString() => 1,
                    DiseaseEnum::GASTROENTERIS->toString() => 1,
                    DiseaseEnum::SMALLPOX->toString() => 1,
                    DiseaseEnum::SYPHILIS->toString() => 1,
                    DiseaseEnum::BLACK_BITE->toString() => 1,
                    DiseaseEnum::REJUVENATION->toString() => 1,
                    DiseaseEnum::SKIN_INFLAMMATION->toString() => 1,
                    DiseaseEnum::SLIGHT_NAUSEA->toString() => 1,
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
