<?php

namespace Mush\Disease\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Modifier\DataFixtures\DiseaseModifierConfigFixtures;
use Mush\Modifier\Entity\ModifierConfig;

class DiseaseConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        /** @var ModifierConfig $reduceMax1HealthPoint */
        $reduceMax1HealthPoint = $this->getReference(DiseaseModifierConfigFixtures::REDUCE_MAX_1_HEALTH_POINT);
        /** @var ModifierConfig $reduceMax2HealthPoint */
        $reduceMax2HealthPoint = $this->getReference(DiseaseModifierConfigFixtures::REDUCE_MAX_2_HEALTH_POINT);
        /** @var ModifierConfig $reduceMax4HealthPoint */
        $reduceMax4HealthPoint = $this->getReference(DiseaseModifierConfigFixtures::REDUCE_MAX_4_HEALTH_POINT);
        /** @var ModifierConfig $reduceMax1MoralPoint */
        $reduceMax1MoralPoint = $this->getReference(DiseaseModifierConfigFixtures::REDUCE_MAX_1_MORAL_POINT);
        /** @var ModifierConfig $reduceMax2MoralPoint */
        $reduceMax2MoralPoint = $this->getReference(DiseaseModifierConfigFixtures::REDUCE_MAX_2_MORAL_POINT);
        /** @var ModifierConfig $cycle1HealthLost */
        $cycle1HealthLost = $this->getReference(DiseaseModifierConfigFixtures::CYCLE_1_HEALTH_LOST);
        /** @var ModifierConfig $cycle2HealthLost */
        $cycle2HealthLost = $this->getReference(DiseaseModifierConfigFixtures::CYCLE_2_HEALTH_LOST);
        /** @var ModifierConfig $cycle4HealthLost */
        $cycle4HealthLost = $this->getReference(DiseaseModifierConfigFixtures::CYCLE_4_HEALTH_LOST);
        /** @var ModifierConfig $cycle1MovementLost */
        $cycle1MovementLost = $this->getReference(DiseaseModifierConfigFixtures::CYCLE_1_MOVEMENT_LOST);
        /** @var ModifierConfig $cycle1SatietyLost */
        $cycle1SatietyLost = $this->getReference(DiseaseModifierConfigFixtures::CYCLE_1_SATIETY_LOST);
        /** @var ModifierConfig $cycle1SatietyIncrease */
        $cycle1SatietyIncrease = $this->getReference(DiseaseModifierConfigFixtures::CYCLE_1_SATIETY_INCREASE);
        /** @var ModifierConfig $cycle1ActionLostRand10 */
        $cycle1ActionLostRand10 = $this->getReference(DiseaseModifierConfigFixtures::CYCLE_1_ACTION_LOST_RAND_10);
        /** @var ModifierConfig $cycle1HealthLostRand10 */
        $cycle1HealthLostRand10 = $this->getReference(DiseaseModifierConfigFixtures::CYCLE_1_HEALTH_LOST_RAND_10);
        /** @var ModifierConfig $cycle1ActionLostRand16 */
        $cycle1ActionLostRand16 = $this->getReference(DiseaseModifierConfigFixtures::CYCLE_1_ACTION_LOST_RAND_16);
        /** @var ModifierConfig $cycle1HealthLostRand16 */
        $cycle1HealthLostRand16 = $this->getReference(DiseaseModifierConfigFixtures::CYCLE_1_HEALTH_LOST_RAND_16);
        /** @var ModifierConfig $cycle1ActionLostRand20 */
        $cycle1ActionLostRand20 = $this->getReference(DiseaseModifierConfigFixtures::CYCLE_1_ACTION_LOST_RAND_20);
        /** @var ModifierConfig $cycle1ActionLostRand30 */
        $cycle1ActionLostRand30 = $this->getReference(DiseaseModifierConfigFixtures::CYCLE_1_ACTION_LOST_RAND_30);
        /** @var ModifierConfig $cycle2ActionLostRand40 */
        $cycle2ActionLostRand40 = $this->getReference(DiseaseModifierConfigFixtures::CYCLE_2_ACTION_LOST_RAND_40);
        /** @var ModifierConfig $cycle1MovementLostRand50 */
        $cycle1MovementLostRand50 = $this->getReference(DiseaseModifierConfigFixtures::CYCLE_1_MOVEMENT_LOST_RAND_50);
        /** @var ModifierConfig $cycle1HealthLostRand50 */
        $cycle1HealthLostRand50 = $this->getReference(DiseaseModifierConfigFixtures::CYCLE_1_HEALTH_LOST_RAND_50);
        /** @var ModifierConfig $consume1ActionLoss */
        $consume1ActionLoss = $this->getReference(DiseaseModifierConfigFixtures::CONSUME_1_ACTION_LOSS);
        /** @var ModifierConfig $consume2ActionLoss */
        $consume2ActionLoss = $this->getReference(DiseaseModifierConfigFixtures::CONSUME_2_ACTION_LOSS);
        /** @var ModifierConfig $moveIncreaseMovement */
        $moveIncreaseMovement = $this->getReference(DiseaseModifierConfigFixtures::MOVE_INCREASE_MOVEMENT);
        /** @var ModifierConfig $infected4HealthLoss */
        $infected4HealthLoss = $this->getReference(DiseaseModifierConfigFixtures::INFECTED_4_HEALTH_LOSS);
        /** @var ModifierConfig $takeCat6HealthLoss */
        $takeCat6HealthLoss = $this->getReference(DiseaseModifierConfigFixtures::TAKE_CAT_6_HEALTH_LOSS);
        /** @var ModifierConfig $shootAction10PercentAccuracyLost */
        $shootAction10PercentAccuracyLost = $this->getReference(DiseaseModifierConfigFixtures::SHOOT_ACTION_10_PERCENT_ACCURACY_LOST);

        $foodPoisoning = new DiseaseConfig();
        $foodPoisoning
            ->setGameConfig($gameConfig)
            ->setName(DiseaseEnum::FOOD_POISONING)
            ->setModifierConfigs(new ArrayCollection([$reduceMax1HealthPoint]))
            ;

        $manager->persist($foodPoisoning);

        $acidReflux = new DiseaseConfig();
        $acidReflux
            ->setGameConfig($gameConfig)
            ->setName(DiseaseEnum::ACID_REFLUX)
            ->setModifierConfigs(new ArrayCollection([$consume2ActionLoss]))
        ;

        $manager->persist($acidReflux);

        $blackBite = new DiseaseConfig();
        $blackBite
            ->setGameConfig($gameConfig)
            ->setName(DiseaseEnum::BLACK_BITE)
            ->setModifierConfigs(new ArrayCollection([
                $cycle1ActionLostRand10,
                $infected4HealthLoss,
                ]))
        ;

        $manager->persist($blackBite);

        $catAllergy = new DiseaseConfig();
        $catAllergy
            ->setGameConfig($gameConfig)
            ->setName(DiseaseEnum::CAT_ALLERGY)
            ->setModifierConfigs(new ArrayCollection([
                $takeCat6HealthLoss,
                ]))
        ;

        $manager->persist($catAllergy);

        $cold = new DiseaseConfig();
        $cold
            ->setGameConfig($gameConfig)
            ->setName(DiseaseEnum::COLD)
            ->setModifierConfigs(new ArrayCollection([$cycle1ActionLostRand20]))
        ;

        $manager->persist($cold);

        $extremeTinnitus = new DiseaseConfig();
        $extremeTinnitus
            ->setGameConfig($gameConfig)
            ->setName(DiseaseEnum::EXTREME_TINNITUS)
            ->setModifierConfigs(new ArrayCollection([$cycle1ActionLostRand16, $reduceMax2MoralPoint]))
        ;

        $manager->persist($extremeTinnitus);

        $flu = new DiseaseConfig();
        $flu
            ->setGameConfig($gameConfig)
            ->setName(DiseaseEnum::FLU)
            ->setModifierConfigs(new ArrayCollection([
                $cycle1HealthLostRand10,
                $cycle1ActionLostRand20,
                $reduceMax2MoralPoint,
                $reduceMax2HealthPoint,
                ]))
        ;

        $manager->persist($flu);

        $fungicInfection = new DiseaseConfig();
        $fungicInfection
            ->setGameConfig($gameConfig)
            ->setName(DiseaseEnum::FUNGIC_INFECTION)
            ->setModifierConfigs(new ArrayCollection([
                $reduceMax2MoralPoint,
                $reduceMax2HealthPoint,
            ]))
        ;

        $manager->persist($fungicInfection);

        $gastroenteritis = new DiseaseConfig();
        $gastroenteritis
            ->setGameConfig($gameConfig)
            ->setName(DiseaseEnum::GASTROENTERIS)
            ->setModifierConfigs(new ArrayCollection([
                $consume1ActionLoss,
                $cycle1HealthLostRand16,
                $cycle1MovementLost,
                $reduceMax1HealthPoint,
            ]))
        ;

        $manager->persist($gastroenteritis);

        $junkbumpkinitis = new DiseaseConfig();
        $junkbumpkinitis
            ->setGameConfig($gameConfig)
            ->setName(DiseaseEnum::JUNKBUMPKINITIS)
        ;

        $manager->persist($junkbumpkinitis);

        $migraine = new DiseaseConfig();
        $migraine
            ->setGameConfig($gameConfig)
            ->setName(DiseaseEnum::MIGRAINE)
            ->setModifierConfigs(new ArrayCollection([$cycle1ActionLostRand20]))
        ;

        $manager->persist($migraine);

        $mushAllergy = new DiseaseConfig();
        $mushAllergy
            ->setGameConfig($gameConfig)
            ->setName(DiseaseEnum::MUSH_ALLERGY)
            ->setModifierConfigs(new ArrayCollection([
                $infected4HealthLoss,
            ]))
        ;

        $manager->persist($mushAllergy);

        $quincksOedema = new DiseaseConfig();
        $quincksOedema
            ->setGameConfig($gameConfig)
            ->setName(DiseaseEnum::QUINCKS_OEDEMA)
            ->setModifierConfigs(new ArrayCollection([
                $moveIncreaseMovement,
                $reduceMax4HealthPoint,
            ]))
        ;

        $manager->persist($quincksOedema);

        $rejuvenation = new DiseaseConfig();
        $rejuvenation
            ->setGameConfig($gameConfig)
            ->setName(DiseaseEnum::REJUVENATION)
            ->setModifierConfigs(new ArrayCollection([
                $cycle1ActionLostRand20,
            ]))
        ;

        $manager->persist($rejuvenation);

        $rubella = new DiseaseConfig();
        $rubella
            ->setGameConfig($gameConfig)
            ->setName(DiseaseEnum::RUBELLA)
            ->setModifierConfigs(new ArrayCollection([
                $cycle1MovementLostRand50,
                $reduceMax1HealthPoint,
                $reduceMax1MoralPoint,
            ]))
        ;

        $manager->persist($rubella);

        $sepsis = new DiseaseConfig();
        $sepsis
            ->setGameConfig($gameConfig)
            ->setName(DiseaseEnum::SEPSIS)
            ->setModifierConfigs(new ArrayCollection([$cycle4HealthLost]))
        ;

        $manager->persist($sepsis);

        $sinusStorm = new DiseaseConfig();
        $sinusStorm
            ->setGameConfig($gameConfig)
            ->setName(DiseaseEnum::SINUS_STORM)
            ->setModifierConfigs(new ArrayCollection([$cycle1ActionLostRand30]))
        ;

        $manager->persist($sinusStorm);

        $skinInflammation = new DiseaseConfig();
        $skinInflammation
            ->setGameConfig($gameConfig)
            ->setName(DiseaseEnum::SKIN_INFLAMMATION)
        ;

        $manager->persist($skinInflammation);

        $nausea = new DiseaseConfig();
        $nausea
            ->setGameConfig($gameConfig)
            ->setName(DiseaseEnum::SLIGHT_NAUSEA)
            ->setModifierConfigs(new ArrayCollection([
                $cycle1SatietyIncrease,
            ]))
        ;

        $manager->persist($nausea);

        $smallPox = new DiseaseConfig();
        $smallPox
            ->setGameConfig($gameConfig)
            ->setName(DiseaseEnum::SMALLPOX)
            ->setModifierConfigs(new ArrayCollection([
                $cycle2ActionLostRand40,
                $cycle1HealthLostRand50,
                $reduceMax2HealthPoint,
                $reduceMax2MoralPoint,
            ]))
        ;

        $manager->persist($smallPox);

        $spaceRabies = new DiseaseConfig();
        $spaceRabies
            ->setGameConfig($gameConfig)
            ->setName(DiseaseEnum::SPACE_RABIES)
            ->setModifierConfigs(new ArrayCollection([
                $cycle2HealthLost,
            ]))
        ;
        $manager->persist($spaceRabies);

        $syphilis = new DiseaseConfig();
        $syphilis
            ->setGameConfig($gameConfig)
            ->setName(DiseaseEnum::SYPHILIS)
            ->setModifierConfigs(new ArrayCollection([
                $reduceMax2MoralPoint,
                $cycle2ActionLostRand40,
                $shootAction10PercentAccuracyLost,
            ]))
        ;

        $manager->persist($syphilis);

        $tapeworm = new DiseaseConfig();
        $tapeworm
            ->setGameConfig($gameConfig)
            ->setName(DiseaseEnum::TAPEWORM)
            ->setModifierConfigs(new ArrayCollection([
                $cycle1SatietyLost,
            ]))
        ;

        $manager->persist($tapeworm);

        $vitaminDeficiency = new DiseaseConfig();
        $vitaminDeficiency
            ->setGameConfig($gameConfig)
            ->setName(DiseaseEnum::VITAMIN_DEFICIENCY)
            ->setModifierConfigs(new ArrayCollection([
                $cycle1ActionLostRand10,
            ]))
        ;

        $manager->persist($vitaminDeficiency);

        $manager->flush();

        $this->addReference(DiseaseEnum::FOOD_POISONING, $foodPoisoning);
        $this->addReference(DiseaseEnum::VITAMIN_DEFICIENCY, $vitaminDeficiency);
        $this->addReference(DiseaseEnum::TAPEWORM, $tapeworm);
        $this->addReference(DiseaseEnum::SYPHILIS, $syphilis);
        $this->addReference(DiseaseEnum::SPACE_RABIES, $spaceRabies);
        $this->addReference(DiseaseEnum::SMALLPOX, $smallPox);
        $this->addReference(DiseaseEnum::SLIGHT_NAUSEA, $nausea);
        $this->addReference(DiseaseEnum::SKIN_INFLAMMATION, $skinInflammation);
        $this->addReference(DiseaseEnum::SINUS_STORM, $sinusStorm);
        $this->addReference(DiseaseEnum::SEPSIS, $sepsis);
        $this->addReference(DiseaseEnum::RUBELLA, $rubella);
        $this->addReference(DiseaseEnum::REJUVENATION, $rejuvenation);
        $this->addReference(DiseaseEnum::QUINCKS_OEDEMA, $quincksOedema);
        $this->addReference(DiseaseEnum::MUSH_ALLERGY, $mushAllergy);
        $this->addReference(DiseaseEnum::MIGRAINE, $migraine);
        $this->addReference(DiseaseEnum::JUNKBUMPKINITIS, $junkbumpkinitis);
        $this->addReference(DiseaseEnum::GASTROENTERIS, $gastroenteritis);
        $this->addReference(DiseaseEnum::FUNGIC_INFECTION, $fungicInfection);
        $this->addReference(DiseaseEnum::FLU, $flu);
        $this->addReference(DiseaseEnum::EXTREME_TINNITUS, $extremeTinnitus);
        $this->addReference(DiseaseEnum::COLD, $cold);
        $this->addReference(DiseaseEnum::CAT_ALLERGY, $catAllergy);
        $this->addReference(DiseaseEnum::BLACK_BITE, $blackBite);
        $this->addReference(DiseaseEnum::ACID_REFLUX, $acidReflux);
    }

    public function getDependencies()
    {
        return [
            GameConfigFixtures::class,
            DiseaseModifierConfigFixtures::class,
        ];
    }
}
