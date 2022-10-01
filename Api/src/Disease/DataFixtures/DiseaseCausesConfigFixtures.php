<?php

namespace Mush\Disease\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\Enum\ActionEnum;
use Mush\Disease\Entity\Config\DiseaseCauseConfig;
use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Enum\DisorderEnum;
use Mush\Disease\Enum\InjuryEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;

class DiseaseCausesConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public const ALIEN_FRUIT_DISEASE_CAUSE_CONFIG = 'alien.fruit.disease.cause.config';
    public const PERISHED_FOOD_DISEASE_CAUSE_CONFIG = 'perished.food.disease.cause.config';
    public const CYCLE_DISEASE_CAUSE_CONFIG = 'cycle.disease.cause.config';
    public const LOW_MORALE_DISEASE_CAUSE_CONFIG = 'cycle.low.morale.disease.cause.config';
    public const MAKE_SICK_DISEASE_CAUSE_CONFIG = 'make.sick.disease.cause.config';
    public const FAKE_DISEASE_DISEASE_CAUSE_CONFIG = 'fake.disease.disease.cause.config';
    public const FAILED_SURGERY_DISEASE_CAUSE_CONFIG = 'failed.surgery.disease.cause.config';
    public const CAT_ALLERGY_DISEASE_CAUSE_CONFIG = 'cat.allergy.disease.cause.config';
    public const INFECTION_DISEASE_CAUSE_CONFIG = 'infection.disease.cause.config';
    public const SEX_DISEASE_CAUSE_CONFIG = 'sex.disease.cause.config';
    public const TRAUMA_DISEASE_CAUSE_CONFIG = 'trauma.disease.cause.config';
    public const CONTACT_DISEASE_CAUSE_CONFIG = 'contact.disease.cause.config';
    public const CRITICAL_FAIL_KNIFE_DISEASE_CAUSE_CONFIG = 'critical.fail.knife.disease.cause.config';
    public const CRITICAL_SUCCESS_KNIFE_DISEASE_CAUSE_CONFIG = 'critical.success.knife.disease.cause.config';
    public const CRITICAL_FAIL_BLASTER_DISEASE_CAUSE_CONFIG = 'critical.fail.blaster.disease.cause.config';
    public const CRITICAL_SUCCESS_BLASTER_DISEASE_CAUSE_CONFIG = 'critical.success.blaster.disease.cause.config';

    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        $diseaseCauseAlienFruit = new DiseaseCauseConfig();
        $diseaseCauseAlienFruit
            ->setGameConfig($gameConfig)
            ->setName(DiseaseCauseEnum::ALIEN_FRUIT)
            ->setDiseases(
                [
                    DiseaseEnum::CAT_ALLERGY => 1,
                    DiseaseEnum::MUSH_ALLERGY => 1,
                    DiseaseEnum::SEPSIS => 1,
                    DiseaseEnum::SLIGHT_NAUSEA => 1,
                    DiseaseEnum::SMALLPOX => 1,
                    DiseaseEnum::SYPHILIS => 1,
                    DisorderEnum::AILUROPHOBIA => 1,
                    DisorderEnum::COPROLALIA => 1,
                    DisorderEnum::SPLEEN => 1,
                    DisorderEnum::WEAPON_PHOBIA => 1,
                    DisorderEnum::CHRONIC_VERTIGO => 1,
                    DisorderEnum::PARANOIA => 1,
                    DiseaseEnum::ACID_REFLUX => 2,
                    DiseaseEnum::SKIN_INFLAMMATION => 2,
                    DisorderEnum::AGORAPHOBIA => 2,
                    DisorderEnum::CHRONIC_MIGRAINE => 2,
                    DisorderEnum::VERTIGO => 2,
                    DisorderEnum::DEPRESSION => 2,
                    DisorderEnum::PSYCHOTIC_EPISODE => 2,
                    DisorderEnum::CRABISM => 4,
                    DiseaseEnum::BLACK_BITE => 4,
                    DiseaseEnum::COLD => 4,
                    DiseaseEnum::EXTREME_TINNITUS => 4,
                    DiseaseEnum::FOOD_POISONING => 4,
                    DiseaseEnum::FUNGIC_INFECTION => 4,
                    DiseaseEnum::REJUVENATION => 4,
                    DiseaseEnum::RUBELLA => 4,
                    DiseaseEnum::SINUS_STORM => 4,
                    DiseaseEnum::SPACE_RABIES => 4,
                    DiseaseEnum::VITAMIN_DEFICIENCY => 4,
                    DiseaseEnum::FLU => 8,
                    DiseaseEnum::GASTROENTERIS => 8,
                    DiseaseEnum::MIGRAINE => 8,
                    DiseaseEnum::TAPEWORM => 8,
                ]
            )
        ;
        $manager->persist($diseaseCauseAlienFruit);

        $diseaseCausePerishedFood = new DiseaseCauseConfig();
        $diseaseCausePerishedFood
            ->setGameConfig($gameConfig)
            ->setName(DiseaseCauseEnum::PERISHED_FOOD)
            ->setDiseases([DiseaseEnum::FOOD_POISONING])
        ;
        $manager->persist($diseaseCausePerishedFood);

        $diseaseCauseCycle = new DiseaseCauseConfig();
        $diseaseCauseCycle
            ->setGameConfig($gameConfig)
            ->setName(DiseaseCauseEnum::CYCLE)
            ->setDiseases(
                [
                    DiseaseEnum::MUSH_ALLERGY => 1,
                    DiseaseEnum::CAT_ALLERGY => 1,
                    DiseaseEnum::FUNGIC_INFECTION => 2,
                    DiseaseEnum::SINUS_STORM => 2,
                    DiseaseEnum::VITAMIN_DEFICIENCY => 4,
                    DiseaseEnum::ACID_REFLUX => 4,
                    DiseaseEnum::MIGRAINE => 4,
                    DiseaseEnum::GASTROENTERIS => 8,
                    DiseaseEnum::COLD => 8,
                    DiseaseEnum::SLIGHT_NAUSEA => 8,
                ]
            );

        $diseaseCauseCycleDepressed = new DiseaseCauseConfig();
        $diseaseCauseCycleDepressed
            ->setGameConfig($gameConfig)
            ->setName(DiseaseCauseEnum::CYCLE_LOW_MORALE)
            ->setDiseases(
                [
                    DiseaseEnum::MUSH_ALLERGY => 1,
                    DiseaseEnum::CAT_ALLERGY => 1,
                    DiseaseEnum::FUNGIC_INFECTION => 2,
                    DiseaseEnum::SINUS_STORM => 2,
                    DiseaseEnum::VITAMIN_DEFICIENCY => 4,
                    DiseaseEnum::ACID_REFLUX => 4,
                    DiseaseEnum::MIGRAINE => 4,
                    DiseaseEnum::GASTROENTERIS => 8,
                    DiseaseEnum::COLD => 8,
                    DiseaseEnum::SLIGHT_NAUSEA => 8,
                    DisorderEnum::DEPRESSION => 32,
                ]
            );
        $manager->persist($diseaseCauseCycleDepressed);
        $manager->persist($diseaseCauseCycle);

        $diseaseCausesBacterialContact = new DiseaseCauseConfig();
        $diseaseCausesBacterialContact
            ->setGameConfig($gameConfig)
            ->setName(ActionEnum::MAKE_SICK)
            ->setDiseases(
                [
                    DiseaseEnum::COLD => 1,
                    DiseaseEnum::FUNGIC_INFECTION => 1,
                    DiseaseEnum::FLU => 1,
                    DiseaseEnum::EXTREME_TINNITUS => 1,
                ]
            );

        $diseaseCausesFakeDisease = new DiseaseCauseConfig();
        $diseaseCausesFakeDisease
            ->setGameConfig($gameConfig)
            ->setName(ActionEnum::FAKE_DISEASE)
            ->setDiseases(
                [
                    DiseaseEnum::CAT_ALLERGY => 1,
                    DiseaseEnum::MUSH_ALLERGY => 1,
                    DiseaseEnum::SEPSIS => 1,
                    DiseaseEnum::SLIGHT_NAUSEA => 1,
                    DiseaseEnum::SMALLPOX => 1,
                    DiseaseEnum::SYPHILIS => 1,
                    DisorderEnum::AILUROPHOBIA => 1,
                    DisorderEnum::COPROLALIA => 1,
                    DisorderEnum::WEAPON_PHOBIA => 1,
                    DisorderEnum::CHRONIC_VERTIGO => 1,
                    DisorderEnum::PARANOIA => 1,
                    DiseaseEnum::ACID_REFLUX => 1,
                    DiseaseEnum::SKIN_INFLAMMATION => 1,
                    DisorderEnum::AGORAPHOBIA => 1,
                    DisorderEnum::CHRONIC_MIGRAINE => 1,
                    DisorderEnum::VERTIGO => 1,
                    DisorderEnum::DEPRESSION => 1,
                    DisorderEnum::PSYCHOTIC_EPISODE => 1,
                    DisorderEnum::CRABISM => 1,
                    DiseaseEnum::BLACK_BITE => 1,
                    DiseaseEnum::COLD => 1,
                    DiseaseEnum::EXTREME_TINNITUS => 1,
                    DiseaseEnum::FUNGIC_INFECTION => 1,
                    DiseaseEnum::REJUVENATION => 1,
                    DiseaseEnum::RUBELLA => 1,
                    DiseaseEnum::SINUS_STORM => 1,
                    DiseaseEnum::SPACE_RABIES => 1,
                    DiseaseEnum::VITAMIN_DEFICIENCY => 1,
                    DiseaseEnum::FLU => 1,
                    DiseaseEnum::MIGRAINE => 1,
                    DiseaseEnum::TAPEWORM => 1,
                    InjuryEnum::CRITICAL_HAEMORRHAGE => 1,
                    InjuryEnum::HAEMORRHAGE => 1,
                    InjuryEnum::BUSTED_ARM_JOINT => 1,
                    InjuryEnum::BRUISED_SHOULDER => 1,
                    InjuryEnum::PUNCTURED_LUNG => 1,
                    InjuryEnum::BURNT_HAND => 1,
                    InjuryEnum::MISSING_FINGER => 1,
                    InjuryEnum::BROKEN_FINGER => 1,
                    InjuryEnum::MASHED_ARMS => 1,
                    InjuryEnum::TORN_TONGUE => 1,
                    InjuryEnum::OPEN_AIR_BRAIN => 1,
                    InjuryEnum::MASHED_FOOT => 1,
                    InjuryEnum::MASHED_LEGS => 1,
                    InjuryEnum::INNER_EAR_DAMAGED => 1,
                    InjuryEnum::BROKEN_SHOULDER => 1,
                    InjuryEnum::BROKEN_FOOT => 1,
                    InjuryEnum::MASHED_HAND => 1,
                    InjuryEnum::BROKEN_RIBS => 1,
                    InjuryEnum::BURNT_ARMS => 1,
                    InjuryEnum::BUSTED_SHOULDER => 1,
                    InjuryEnum::BURNS_90_OF_BODY => 1,
                    InjuryEnum::DAMAGED_EARS => 1,
                    InjuryEnum::BROKEN_LEG => 1,
                ]
            );

        $manager->persist($diseaseCausesFakeDisease);

        $diseaseCauseFailedSurgery = new DiseaseCauseConfig();
        $diseaseCauseFailedSurgery
            ->setGameConfig($gameConfig)
            ->setName(ActionEnum::SURGERY)
            ->setDiseases([DiseaseEnum::SEPSIS])
        ;
        $manager->persist($diseaseCauseFailedSurgery);

        $diseaseCauseCatAllergy = new DiseaseCauseConfig();
        $diseaseCauseCatAllergy
            ->setGameConfig($gameConfig)
            ->setName(DiseaseEnum::CAT_ALLERGY)
            ->setDiseases([
                InjuryEnum::BURNT_ARMS => 1,
                InjuryEnum::BURNT_HAND => 1,
                ])
        ;
        $manager->persist($diseaseCauseCatAllergy);

        $diseaseCauseInfection = new DiseaseCauseConfig();
        $diseaseCauseInfection
            ->setGameConfig($gameConfig)
            ->setName(DiseaseCauseEnum::INFECTION)
            ->setDiseases([
                DiseaseEnum::FLU => 50,
                DiseaseEnum::GASTROENTERIS => 20,
                DiseaseEnum::FUNGIC_INFECTION => 15,
                DiseaseEnum::MIGRAINE => 10,
                DiseaseEnum::MUSH_ALLERGY => 5,
                ])
        ;
        $manager->persist($diseaseCauseInfection);

        $diseaseCauseSex = new DiseaseCauseConfig();
        $diseaseCauseSex
            ->setGameConfig($gameConfig)
            ->setName(DiseaseCauseEnum::SEX)
            ->setDiseases([
                DiseaseEnum::FLU => 1,
                DiseaseEnum::GASTROENTERIS => 1,
                DiseaseEnum::SKIN_INFLAMMATION => 1,
            ])
        ;
        $manager->persist($diseaseCauseSex);

        $diseaseCauseTrauma = new DiseaseCauseConfig();
        $diseaseCauseTrauma
            ->setGameConfig($gameConfig)
            ->setName(DiseaseCauseEnum::TRAUMA)
            ->setDiseases(
                [
                    DiseaseEnum::MIGRAINE => 30,
                    DiseaseEnum::GASTROENTERIS => 30,
                    DisorderEnum::CHRONIC_MIGRAINE => 6,
                    DisorderEnum::PSYCHOTIC_EPISODE => 6,
                    DisorderEnum::WEAPON_PHOBIA => 6,
                    DisorderEnum::PARANOIA => 6,
                    DisorderEnum::CRABISM => 6,
                    DisorderEnum::COPROLALIA => 6,
                    DisorderEnum::DEPRESSION => 6,
                    DisorderEnum::AGORAPHOBIA => 3,
                    DisorderEnum::CHRONIC_VERTIGO => 3,
                    DisorderEnum::SPLEEN => 1,
                ]
            );
        $manager->persist($diseaseCauseTrauma);

        $diseaseCauseContact = new DiseaseCauseConfig();
        $diseaseCauseContact
            ->setGameConfig($gameConfig)
            ->setName(DiseaseCauseEnum::CONTACT)
            ->setDiseases(
                [
                    DiseaseEnum::FLU => 1,
                    DiseaseEnum::GASTROENTERIS => 1,
                    DiseaseEnum::SKIN_INFLAMMATION => 1,
                ]
            );
        $manager->persist($diseaseCauseContact);

        $diseaseCauseCriticalFailKnife = new DiseaseCauseConfig();
        $diseaseCauseCriticalFailKnife
            ->setGameConfig($gameConfig)
            ->setName(DiseaseCauseEnum::CRITICAL_FAIL_KNIFE)
            ->setDiseases(
                [
                    InjuryEnum::TORN_TONGUE => 1,
                    InjuryEnum::BUSTED_SHOULDER => 1,
                ]
            );
        $manager->persist($diseaseCauseCriticalFailKnife);

        $diseaseCauseCriticalSuccessKnife = new DiseaseCauseConfig();
        $diseaseCauseCriticalSuccessKnife
            ->setGameConfig($gameConfig)
            ->setName(DiseaseCauseEnum::CRITICAL_SUCCESS_KNIFE)
            ->setDiseases(
                [
                    InjuryEnum::CRITICAL_HAEMORRHAGE => 30,
                    InjuryEnum::HAEMORRHAGE => 20,
                    InjuryEnum::BUSTED_ARM_JOINT => 14,
                    InjuryEnum::BRUISED_SHOULDER => 7,
                    InjuryEnum::PUNCTURED_LUNG => 5,
                    InjuryEnum::BURNT_HAND => 3,
                    InjuryEnum::MISSING_FINGER => 2,
                    InjuryEnum::BROKEN_FINGER => 2,
                    InjuryEnum::MASHED_ARMS => 2,
                    InjuryEnum::TORN_TONGUE => 2,
                    InjuryEnum::OPEN_AIR_BRAIN => 2,
                    InjuryEnum::MASHED_FOOT => 2,
                    InjuryEnum::MASHED_LEGS => 2,
                    InjuryEnum::INNER_EAR_DAMAGED => 2,
                    InjuryEnum::BROKEN_SHOULDER => 2,
                    InjuryEnum::BROKEN_FOOT => 2,
                    InjuryEnum::MASHED_HAND => 1,
                    InjuryEnum::BROKEN_RIBS => 1,
                    InjuryEnum::BURNT_ARMS => 1,
                    InjuryEnum::BUSTED_SHOULDER => 1,
                    InjuryEnum::BURNS_90_OF_BODY => 1,
                    InjuryEnum::DAMAGED_EARS => 1,
                    InjuryEnum::BROKEN_LEG => 1,
                ]
            );
        $manager->persist($diseaseCauseCriticalSuccessKnife);

        $diseaseCauseCriticalFailBlaster = new DiseaseCauseConfig();
        $diseaseCauseCriticalFailBlaster
            ->setGameConfig($gameConfig)
            ->setName(DiseaseCauseEnum::CRITICAL_FAIL_BLASTER)
            ->setDiseases(
                [
                    InjuryEnum::BROKEN_LEG => 1,
                    InjuryEnum::BROKEN_SHOULDER => 1,
                ]
            );
        $manager->persist($diseaseCauseCriticalFailBlaster);

        $diseaseCauseCriticalSuccessBlaster = new DiseaseCauseConfig();
        $diseaseCauseCriticalSuccessBlaster
            ->setGameConfig($gameConfig)
            ->setName(DiseaseCauseEnum::CRITICAL_SUCCESS_BLASTER)
            ->setDiseases(
                [
                    InjuryEnum::DAMAGED_EARS => 10,
                    InjuryEnum::CRITICAL_HAEMORRHAGE => 2,
                    InjuryEnum::OPEN_AIR_BRAIN => 2,
                    InjuryEnum::BURNS_90_OF_BODY => 2,
                    InjuryEnum::TORN_TONGUE => 2,
                    InjuryEnum::PUNCTURED_LUNG => 1,
                    InjuryEnum::HAEMORRHAGE => 1,
                    InjuryEnum::BROKEN_SHOULDER => 1,
                    InjuryEnum::HEAD_TRAUMA => 1,
                    InjuryEnum::BURNS_50_OF_BODY => 1,
                ]
            );
        $manager->persist($diseaseCauseCriticalSuccessBlaster);

        $manager->flush();

        $this->addReference(self::ALIEN_FRUIT_DISEASE_CAUSE_CONFIG, $diseaseCauseAlienFruit);
        $this->addReference(self::PERISHED_FOOD_DISEASE_CAUSE_CONFIG, $diseaseCausePerishedFood);
        $this->addReference(self::CYCLE_DISEASE_CAUSE_CONFIG, $diseaseCauseCycle);
        $this->addReference(self::LOW_MORALE_DISEASE_CAUSE_CONFIG, $diseaseCauseCycleDepressed);
        $this->addReference(self::MAKE_SICK_DISEASE_CAUSE_CONFIG, $diseaseCausesBacterialContact);
        $this->addReference(self::FAKE_DISEASE_DISEASE_CAUSE_CONFIG, $diseaseCausesFakeDisease);
        $this->addReference(self::FAILED_SURGERY_DISEASE_CAUSE_CONFIG, $diseaseCausesFakeDisease);
        $this->addReference(self::CAT_ALLERGY_DISEASE_CAUSE_CONFIG, $diseaseCauseCatAllergy);
        $this->addReference(self::INFECTION_DISEASE_CAUSE_CONFIG, $diseaseCauseInfection);
        $this->addReference(self::SEX_DISEASE_CAUSE_CONFIG, $diseaseCauseSex);
        $this->addReference(self::TRAUMA_DISEASE_CAUSE_CONFIG, $diseaseCauseTrauma);
        $this->addReference(self::CONTACT_DISEASE_CAUSE_CONFIG, $diseaseCauseContact);
        $this->addReference(self::CRITICAL_FAIL_KNIFE_DISEASE_CAUSE_CONFIG, $diseaseCauseCriticalFailKnife);
        $this->addReference(self::CRITICAL_SUCCESS_KNIFE_DISEASE_CAUSE_CONFIG, $diseaseCauseCriticalSuccessKnife);
        $this->addReference(self::CRITICAL_FAIL_BLASTER_DISEASE_CAUSE_CONFIG, $diseaseCauseCriticalFailBlaster);
        $this->addReference(self::CRITICAL_SUCCESS_BLASTER_DISEASE_CAUSE_CONFIG, $diseaseCauseCriticalSuccessBlaster);
    }

    public function getDependencies()
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
