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
            )
            ->setDiseasesRate(20)
        ;

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
            )
            ->setDiseasesRate(20)
        ;
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
            )
            ->setDiseasesDelayMin(1)
            ->setDiseasesDelayLength(4)
        ;

        $diseaseCausesFakeDisease = new DiseaseCauseConfig();
        $diseaseCausesFakeDisease
            ->setGameConfig($gameConfig)
            ->setName(ActionEnum::FAKE_DISEASE)
            ->setDiseases(
                [
                    DiseaseEnum::COLD => 1,
                    DiseaseEnum::EXTREME_TINNITUS => 1,
                    DiseaseEnum::CAT_ALLERGY => 1,
                    DiseaseEnum::SINUS_STORM => 1,
                ]
            )
        ;

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
            ->setDiseasesRate(2)
            ->setDiseasesDelayMin(2)
            ->setDiseasesDelayLength(4)
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
            ->setDiseasesRate(5)
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
            )
            ->setDiseasesRate(33)
        ;
        $manager->persist($diseaseCauseTrauma);
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
    }

    public function getDependencies()
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
