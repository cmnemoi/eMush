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
use Mush\Game\Enum\GameConfigEnum;

class DiseaseCausesConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public const ALIEN_FRUIT_DISEASE_CAUSE_CONFIG = 'alien.fruit.disease.cause.config';

    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        $diseaseCauseAlienFruit = new DiseaseCauseConfig();
        $diseaseCauseAlienFruit
            ->setCauseName(DiseaseCauseEnum::ALIEN_FRUIT)
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
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($diseaseCauseAlienFruit);

        $diseaseCausePerishedFood = new DiseaseCauseConfig();
        $diseaseCausePerishedFood
            ->setCauseName(DiseaseCauseEnum::PERISHED_FOOD)
            ->setDiseases([DiseaseEnum::FOOD_POISONING => 1])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($diseaseCausePerishedFood);

        $diseaseCauseCycle = new DiseaseCauseConfig();
        $diseaseCauseCycle
            ->setCauseName(DiseaseCauseEnum::CYCLE)
            ->setDiseases([
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
            ])
            ->buildName(GameConfigEnum::DEFAULT);

        $diseaseCauseCycleDepressed = new DiseaseCauseConfig();
        $diseaseCauseCycleDepressed
            ->setCauseName(DiseaseCauseEnum::CYCLE_LOW_MORALE)
            ->setDiseases([
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
            ])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($diseaseCauseCycleDepressed);
        $manager->persist($diseaseCauseCycle);

        $diseaseCausesBacterialContact = new DiseaseCauseConfig();
        $diseaseCausesBacterialContact
            ->setCauseName(ActionEnum::MAKE_SICK->value)
            ->setDiseases([
                DiseaseEnum::COLD => 1,
                DiseaseEnum::FUNGIC_INFECTION => 1,
                DiseaseEnum::FLU => 1,
                DiseaseEnum::EXTREME_TINNITUS => 1,
            ])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($diseaseCausesBacterialContact);

        $diseaseCausesFakeDisease = new DiseaseCauseConfig();
        $diseaseCausesFakeDisease
            ->setCauseName(ActionEnum::FAKE_DISEASE->value)
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
            )
            ->buildName(GameConfigEnum::DEFAULT);

        $manager->persist($diseaseCausesFakeDisease);

        $diseaseCauseFailedSurgery = new DiseaseCauseConfig();
        $diseaseCauseFailedSurgery
            ->setCauseName(ActionEnum::SURGERY->value)
            ->setDiseases([DiseaseEnum::SEPSIS => 1])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($diseaseCauseFailedSurgery);

        $diseaseCauseCatAllergy = new DiseaseCauseConfig();
        $diseaseCauseCatAllergy
            ->setCauseName(DiseaseEnum::CAT_ALLERGY)
            ->setDiseases([
                InjuryEnum::BURNT_ARMS => 1,
                InjuryEnum::BURNT_HAND => 1,
            ])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($diseaseCauseCatAllergy);

        $diseaseCauseInfection = new DiseaseCauseConfig();
        $diseaseCauseInfection
            ->setCauseName(DiseaseCauseEnum::INFECTION)
            ->setDiseases([
                DiseaseEnum::FLU => 50,
                DiseaseEnum::GASTROENTERIS => 20,
                DiseaseEnum::FUNGIC_INFECTION => 15,
                DiseaseEnum::MIGRAINE => 10,
                DiseaseEnum::MUSH_ALLERGY => 5,
            ])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($diseaseCauseInfection);

        $diseaseCauseSex = new DiseaseCauseConfig();
        $diseaseCauseSex
            ->setCauseName(DiseaseCauseEnum::SEX)
            ->setDiseases([
                DiseaseEnum::FLU => 1,
                DiseaseEnum::GASTROENTERIS => 1,
                DiseaseEnum::SKIN_INFLAMMATION => 1,
            ])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($diseaseCauseSex);

        $diseaseCauseTrauma = new DiseaseCauseConfig();
        $diseaseCauseTrauma
            ->setCauseName(DiseaseCauseEnum::TRAUMA)
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
            )
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($diseaseCauseTrauma);

        $diseaseCauseContact = new DiseaseCauseConfig();
        $diseaseCauseContact
            ->setCauseName(DiseaseCauseEnum::CONTACT)
            ->setDiseases(
                [
                    DiseaseEnum::FLU => 1,
                    DiseaseEnum::GASTROENTERIS => 1,
                    DiseaseEnum::SKIN_INFLAMMATION => 1,
                ]
            )
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($diseaseCauseContact);

        $diseaseCauseCriticalFailKnife = new DiseaseCauseConfig();
        $diseaseCauseCriticalFailKnife
            ->setCauseName(DiseaseCauseEnum::CRITICAL_FAIL_KNIFE)
            ->setDiseases(
                [
                    InjuryEnum::TORN_TONGUE => 1,
                    InjuryEnum::BUSTED_SHOULDER => 1,
                ]
            )
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($diseaseCauseCriticalFailKnife);

        $diseaseCauseCriticalSuccessKnife = new DiseaseCauseConfig();
        $diseaseCauseCriticalSuccessKnife
            ->setCauseName(DiseaseCauseEnum::CRITICAL_SUCCESS_KNIFE)
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
            )
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($diseaseCauseCriticalSuccessKnife);

        $diseaseCauseCriticalFailBlaster = new DiseaseCauseConfig();
        $diseaseCauseCriticalFailBlaster
            ->setCauseName(DiseaseCauseEnum::CRITICAL_FAIL_BLASTER)
            ->setDiseases(
                [
                    InjuryEnum::BROKEN_LEG => 1,
                    InjuryEnum::BROKEN_SHOULDER => 1,
                ]
            )
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($diseaseCauseCriticalFailBlaster);

        $diseaseCauseCriticalSuccessBlaster = new DiseaseCauseConfig();
        $diseaseCauseCriticalSuccessBlaster
            ->setCauseName(DiseaseCauseEnum::CRITICAL_SUCCESS_BLASTER)
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
            )
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($diseaseCauseCriticalSuccessBlaster);

        $alienFight = new DiseaseCauseConfig();
        $alienFight
            ->setCauseName(DiseaseCauseEnum::ALIEN_FIGHT)
            ->setDiseases(
                [
                    DiseaseEnum::FLU => 1,
                    DiseaseEnum::SYPHILIS => 1,
                    DiseaseEnum::BLACK_BITE => 1,
                    DiseaseEnum::REJUVENATION => 1,
                    DisorderEnum::AILUROPHOBIA => 1,
                    DiseaseEnum::SPACE_RABIES => 1,
                    DiseaseEnum::SEPSIS => 1,
                ]
            )
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($alienFight);

        $diseaseCauseExploration = new DiseaseCauseConfig();
        $diseaseCauseExploration
            ->setCauseName(DiseaseCauseEnum::EXPLORATION)
            ->setDiseases(
                [
                    DiseaseEnum::MIGRAINE => 1,
                    DiseaseEnum::ACID_REFLUX => 1,
                    DiseaseEnum::FLU => 1,
                    DiseaseEnum::RUBELLA => 1,
                    DiseaseEnum::GASTROENTERIS => 1,
                    DiseaseEnum::SMALLPOX => 1,
                    DiseaseEnum::SKIN_INFLAMMATION => 1,
                    DiseaseEnum::SLIGHT_NAUSEA => 1,
                ]
            )
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($diseaseCauseExploration);

        $gameConfig
            ->addDiseaseCauseConfig($diseaseCauseAlienFruit)
            ->addDiseaseCauseConfig($diseaseCausePerishedFood)
            ->addDiseaseCauseConfig($diseaseCauseCycle)
            ->addDiseaseCauseConfig($diseaseCauseCycleDepressed)
            ->addDiseaseCauseConfig($diseaseCausesBacterialContact)
            ->addDiseaseCauseConfig($diseaseCausesFakeDisease)
            ->addDiseaseCauseConfig($diseaseCauseFailedSurgery)
            ->addDiseaseCauseConfig($diseaseCauseInfection)
            ->addDiseaseCauseConfig($diseaseCauseSex)
            ->addDiseaseCauseConfig($diseaseCauseTrauma)
            ->addDiseaseCauseConfig($diseaseCauseContact)
            ->addDiseaseCauseConfig($diseaseCauseCriticalFailKnife)
            ->addDiseaseCauseConfig($diseaseCauseCriticalSuccessKnife)
            ->addDiseaseCauseConfig($diseaseCauseCriticalFailBlaster)
            ->addDiseaseCauseConfig($diseaseCauseCriticalSuccessBlaster)
            ->addDiseaseCauseConfig($alienFight)
            ->addDiseaseCauseConfig($diseaseCauseExploration);

        $manager->persist($gameConfig);

        $manager->flush();

        $this->addReference(self::ALIEN_FRUIT_DISEASE_CAUSE_CONFIG, $diseaseCauseAlienFruit);
    }

    public function getDependencies()
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
