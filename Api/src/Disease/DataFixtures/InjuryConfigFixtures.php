<?php

namespace Mush\Disease\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Enum\InjuryEnum;
use Mush\Disease\Enum\MedicalConditionTypeEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Modifier\DataFixtures\DiseaseModifierConfigFixtures;
use Mush\Modifier\DataFixtures\DisorderModifierConfigFixtures;
use Mush\Modifier\DataFixtures\InjuryModifierConfigFixtures;
use Mush\Modifier\Entity\Config\EventModifierConfig;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;

class InjuryConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        /** @var VariableEventModifierConfig $consume2ActionLoss */
        $consume2ActionLoss = $this->getReference(DiseaseModifierConfigFixtures::CONSUME_2_ACTION_LOSS);
        /** @var VariableEventModifierConfig $cycle1HealthLost */
        $cycle1HealthLost = $this->getReference(DiseaseModifierConfigFixtures::CYCLE_1_HEALTH_LOST);
        /** @var VariableEventModifierConfig $cycle2HealthLost */
        $cycle2HealthLost = $this->getReference(DiseaseModifierConfigFixtures::CYCLE_2_HEALTH_LOST);
        /** @var VariableEventModifierConfig $increaseCycleDiseaseChances10 */
        $increaseCycleDiseaseChances10 = $this->getReference(DiseaseModifierConfigFixtures::INCREASE_CYCLE_DISEASE_CHANCES_10);
        /** @var VariableEventModifierConfig $moveIncreaseMovement */
        $moveIncreaseMovement = $this->getReference(DiseaseModifierConfigFixtures::MOVE_INCREASE_MOVEMENT);
        /** @var VariableEventModifierConfig $notMoveAction1Increase */
        $notMoveAction1Increase = $this->getReference(InjuryModifierConfigFixtures::NOT_MOVE_ACTION_1_INCREASE);
        /** @var VariableEventModifierConfig $notMoveAction2Increase */
        $notMoveAction2Increase = $this->getReference(InjuryModifierConfigFixtures::NOT_MOVE_ACTION_2_INCREASE);
        /** @var VariableEventModifierConfig $notMoveAction3Increase */
        $notMoveAction3Increase = $this->getReference(InjuryModifierConfigFixtures::NOT_MOVE_ACTION_3_INCREASE);
        /** @var VariableEventModifierConfig $reduceMax1HealthPoint */
        $reduceMax1HealthPoint = $this->getReference(DiseaseModifierConfigFixtures::REDUCE_MAX_1_HEALTH_POINT);
        /** @var VariableEventModifierConfig $reduceMax2HealthPoint */
        $reduceMax2HealthPoint = $this->getReference(DiseaseModifierConfigFixtures::REDUCE_MAX_2_HEALTH_POINT);
        /** @var VariableEventModifierConfig $reduceMax1MoralPoint */
        $reduceMax1MoralPoint = $this->getReference(DiseaseModifierConfigFixtures::REDUCE_MAX_1_MORAL_POINT);
        /** @var VariableEventModifierConfig $reduceMax2MoralPoint */
        $reduceMax2MoralPoint = $this->getReference(DiseaseModifierConfigFixtures::REDUCE_MAX_2_MORAL_POINT);
        /** @var VariableEventModifierConfig $reduceMax3MoralPoint */
        $reduceMax3MoralPoint = $this->getReference(DisorderModifierConfigFixtures::REDUCE_MAX_3_MORAL_POINT);
        /** @var VariableEventModifierConfig $reduceMax3MovementPoint */
        $reduceMax3MovementPoint = $this->getReference(InjuryModifierConfigFixtures::REDUCE_MAX_3_MOVEMENT_POINT);
        /** @var VariableEventModifierConfig $reduceMax5MovementPoint */
        $reduceMax5MovementPoint = $this->getReference(InjuryModifierConfigFixtures::REDUCE_MAX_5_MOVEMENT_POINT);
        /** @var VariableEventModifierConfig $reduceMax12MovementPoint */
        $reduceMax12MovementPoint = $this->getReference(InjuryModifierConfigFixtures::REDUCE_MAX_12_MOVEMENT_POINT);
        /** @var VariableEventModifierConfig $shootAction10PercentAccuracyLost */
        $shootAction10PercentAccuracyLost = $this->getReference(DiseaseModifierConfigFixtures::SHOOT_ACTION_10_PERCENT_ACCURACY_LOST);
        /** @var VariableEventModifierConfig $shootAction20PercentAccuracyLost */
        $shootAction20PercentAccuracyLost = $this->getReference(InjuryModifierConfigFixtures::SHOOT_ACTION_20_PERCENT_ACCURACY_LOST);
        /** @var VariableEventModifierConfig $shootAction40PercentAccuracyLost */
        $shootAction40PercentAccuracyLost = $this->getReference(InjuryModifierConfigFixtures::SHOOT_ACTION_40_PERCENT_ACCURACY_LOST);
        /** @var EventModifierConfig $deafListen */
        $deafListen = $this->getReference(InjuryModifierConfigFixtures::DEAF_LISTEN_MODIFIER);
        /** @var EventModifierConfig $deafSpeak */
        $deafSpeak = $this->getReference(InjuryModifierConfigFixtures::DEAF_SPEAK_MODIFIER);

        /** @var EventModifierConfig $cantMove */
        $cantMove = $this->getReference(InjuryModifierConfigFixtures::CANNOT_MOVE);
        /** @var EventModifierConfig $cantPickUpHeavyItems */
        $cantPickUpHeavyItems = $this->getReference(InjuryModifierConfigFixtures::PREVENT_PICK_HEAVY_ITEMS);
        /** @var EventModifierConfig $consumeVomiting */
        $consumeVomiting = $this->getReference(InjuryModifierConfigFixtures::CONSUME_VOMITING);
        /** @var EventModifierConfig $drooling */
        $drooling = $this->getReference(InjuryModifierConfigFixtures::DROOLING);
        /** @var EventModifierConfig $moveVomiting */
        $moveVomiting = $this->getReference(InjuryModifierConfigFixtures::MOVE_VOMITING);
        /** @var EventModifierConfig $mute */
        $mute = $this->getReference(InjuryModifierConfigFixtures::MUTE_MODIFIER);
        /** @var EventModifierConfig $noPilotingActions */
        $noPilotingActions = $this->getReference(InjuryModifierConfigFixtures::PREVENT_PILOTING);
        /** @var EventModifierConfig $septicemiaOnCycleChange */
        $septicemiaOnCycleChange = $this->getReference(InjuryModifierConfigFixtures::SEPTICEMIA_ON_CYCLE_CHANGE);
        /** @var EventModifierConfig $septicemiaOnDirtyEvent */
        $septicemiaOnDirtyEvent = $this->getReference(InjuryModifierConfigFixtures::SEPTICEMIA_ON_DIRTY_EVENT);
        /** @var EventModifierConfig $septicemiaOnPostAction */
        $septicemiaOnPostAction = $this->getReference(InjuryModifierConfigFixtures::SEPTICEMIA_ON_POST_ACTION);

        // burn
        $burns50OfBody = new DiseaseConfig();
        $burns50OfBody
            ->setDiseaseName(InjuryEnum::BURNS_50_OF_BODY)
            ->setType(MedicalConditionTypeEnum::INJURY)
            ->setModifierConfigs([
                $notMoveAction1Increase,
                $increaseCycleDiseaseChances10,
                ])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($burns50OfBody);

        $burns90OfBody = new DiseaseConfig();
        $burns90OfBody
            ->setDiseaseName(InjuryEnum::BURNS_90_OF_BODY)
            ->setType(MedicalConditionTypeEnum::INJURY)
            ->setModifierConfigs([
                $increaseCycleDiseaseChances10,
                $septicemiaOnCycleChange,
                $septicemiaOnDirtyEvent,
                $septicemiaOnPostAction,
                ])
            ->setOverride([InjuryEnum::BURNS_50_OF_BODY])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($burns90OfBody);

        $burstNose = new DiseaseConfig();
        $burstNose
            ->setDiseaseName(InjuryEnum::BURST_NOSE)
            ->setType(MedicalConditionTypeEnum::INJURY)
            ->setModifierConfigs([
                $reduceMax2MoralPoint,
            ])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($burstNose);

        // Haemorrhage
        $criticalHaemorrhage = new DiseaseConfig();
        $criticalHaemorrhage
            ->setDiseaseName(InjuryEnum::CRITICAL_HAEMORRHAGE)
            ->setType(MedicalConditionTypeEnum::INJURY)
            ->setModifierConfigs([
                $cycle2HealthLost,
                $reduceMax2HealthPoint,
            ])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($criticalHaemorrhage);

        $haemorrhage = new DiseaseConfig();
        $haemorrhage
            ->setDiseaseName(InjuryEnum::HAEMORRHAGE)
            ->setType(MedicalConditionTypeEnum::INJURY)
            ->setModifierConfigs([
                $cycle1HealthLost,
                $reduceMax1HealthPoint,
            ])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($haemorrhage);

        $minorHaemorrhage = new DiseaseConfig();
        $minorHaemorrhage
            ->setDiseaseName(InjuryEnum::MINOR_HAEMORRHAGE)
            ->setType(MedicalConditionTypeEnum::INJURY)
            ->setModifierConfigs([
                $cycle1HealthLost,
            ])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($minorHaemorrhage);

        // Ears
        $damagedEars = new DiseaseConfig();
        $damagedEars
            ->setDiseaseName(InjuryEnum::DAMAGED_EARS)
            ->setType(MedicalConditionTypeEnum::INJURY)
            ->setModifierConfigs([$deafListen, $deafSpeak])

            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($damagedEars);

        $destroyedEars = new DiseaseConfig();
        $destroyedEars
            ->setDiseaseName(InjuryEnum::DESTROYED_EARS)
            ->setType(MedicalConditionTypeEnum::INJURY)
            ->setModifierConfigs([
                $reduceMax1MoralPoint,
                $deafListen,
                $deafSpeak,
            ])
            ->setOverride([InjuryEnum::DAMAGED_EARS])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($destroyedEars);

        $headTrauma = new DiseaseConfig();
        $headTrauma
            ->setDiseaseName(InjuryEnum::HEAD_TRAUMA)
            ->setType(MedicalConditionTypeEnum::INJURY)
            ->setModifierConfigs([
                $reduceMax3MoralPoint,
                $septicemiaOnCycleChange,
                $septicemiaOnDirtyEvent,
                $septicemiaOnPostAction,
            ])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($headTrauma);

        $openAirBrain = new DiseaseConfig();
        $openAirBrain
            ->setDiseaseName(InjuryEnum::OPEN_AIR_BRAIN)
            ->setType(MedicalConditionTypeEnum::INJURY)
            ->setModifierConfigs([
                $reduceMax2MoralPoint,
                $septicemiaOnCycleChange,
                $septicemiaOnDirtyEvent,
                $septicemiaOnPostAction,
            ])
            ->setOverride([InjuryEnum::HEAD_TRAUMA])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($openAirBrain);

        $implantedBullet = new DiseaseConfig();
        $implantedBullet
            ->setDiseaseName(InjuryEnum::IMPLANTED_BULLET)
            ->setType(MedicalConditionTypeEnum::INJURY)
            ->setModifierConfigs([
                $notMoveAction1Increase,
            ])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($implantedBullet);

        $innerEarDamaged = new DiseaseConfig();
        $innerEarDamaged
            ->setDiseaseName(InjuryEnum::INNER_EAR_DAMAGED)
            ->setType(MedicalConditionTypeEnum::INJURY)
            ->setModifierConfigs([
                $notMoveAction1Increase,
                $moveIncreaseMovement,
                $noPilotingActions,
            ])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($innerEarDamaged);

        $tornTongue = new DiseaseConfig();
        $tornTongue
            ->setDiseaseName(InjuryEnum::TORN_TONGUE)
            ->setType(MedicalConditionTypeEnum::INJURY)
            ->setModifierConfigs([
                $mute,
            ])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($tornTongue);

        // foot and leg
        $brokenFoot = new DiseaseConfig();
        $brokenFoot
            ->setDiseaseName(InjuryEnum::BROKEN_FOOT)
            ->setType(MedicalConditionTypeEnum::INJURY)
            ->setModifierConfigs([
                $moveIncreaseMovement,
            ])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($brokenFoot);

        $mashedFoot = new DiseaseConfig();
        $mashedFoot
            ->setDiseaseName(InjuryEnum::MASHED_FOOT)
            ->setType(MedicalConditionTypeEnum::INJURY)
            ->setModifierConfigs([
                $moveIncreaseMovement,
                $reduceMax3MovementPoint,
            ])
            ->setOverride([InjuryEnum::BROKEN_FOOT])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($mashedFoot);

        $brokenLeg = new DiseaseConfig();
        $brokenLeg
            ->setDiseaseName(InjuryEnum::BROKEN_LEG)
            ->setType(MedicalConditionTypeEnum::INJURY)
            ->setModifierConfigs([
                $reduceMax5MovementPoint,
                $moveIncreaseMovement,
            ])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($brokenLeg);

        $mashedLegs = new DiseaseConfig();
        $mashedLegs
            ->setDiseaseName(InjuryEnum::MASHED_LEGS)
            ->setType(MedicalConditionTypeEnum::INJURY)
            ->setModifierConfigs([
                $reduceMax12MovementPoint,
                $cantMove,
            ])
            ->setOverride([InjuryEnum::BROKEN_LEG, InjuryEnum::BROKEN_FOOT, InjuryEnum::MASHED_FOOT])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($mashedLegs);

        // Torso
        $disfunctionalLiver = new DiseaseConfig();
        $disfunctionalLiver
            ->setDiseaseName(InjuryEnum::DYSFUNCTIONAL_LIVER)
            ->setType(MedicalConditionTypeEnum::INJURY)
            ->setModifierConfigs([
                $notMoveAction1Increase,
                $consume2ActionLoss,
                $consumeVomiting,
                $drooling,
                $moveVomiting,
            ])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($disfunctionalLiver);

        $puncturedLung = new DiseaseConfig();
        $puncturedLung
            ->setDiseaseName(InjuryEnum::PUNCTURED_LUNG)
            ->setType(MedicalConditionTypeEnum::INJURY)
            ->setModifierConfigs([
                $cycle2HealthLost,
                $notMoveAction2Increase,
                $mute,
            ])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($puncturedLung);

        $brokenRibs = new DiseaseConfig();
        $brokenRibs
            ->setDiseaseName(InjuryEnum::BROKEN_RIBS)
            ->setType(MedicalConditionTypeEnum::INJURY)
            ->setModifierConfigs([
                $shootAction20PercentAccuracyLost,
            ])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($brokenRibs);

        // finger, arm and shoulder
        $brokenFinger = new DiseaseConfig();
        $brokenFinger
            ->setDiseaseName(InjuryEnum::BROKEN_FINGER)
            ->setType(MedicalConditionTypeEnum::INJURY)
            ->setModifierConfigs([
                $notMoveAction1Increase,
            ])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($brokenFinger);

        $missingFinger = new DiseaseConfig();
        $missingFinger
            ->setDiseaseName(InjuryEnum::MISSING_FINGER)
            ->setType(MedicalConditionTypeEnum::INJURY)
            ->setModifierConfigs([
                $notMoveAction1Increase,
            ])
            ->setOverride([InjuryEnum::BROKEN_FINGER])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($missingFinger);

        $burntHand = new DiseaseConfig();
        $burntHand
            ->setDiseaseName(InjuryEnum::BURNT_HAND)
            ->setType(MedicalConditionTypeEnum::INJURY)
            ->setModifierConfigs([
                $notMoveAction1Increase,
                $reduceMax1HealthPoint,
            ])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($burntHand);

        $mashedHand = new DiseaseConfig();
        $mashedHand
            ->setDiseaseName(InjuryEnum::MASHED_HAND)
            ->setType(MedicalConditionTypeEnum::INJURY)
            ->setModifierConfigs([
                $notMoveAction2Increase,
                $shootAction40PercentAccuracyLost,
            ])
            ->setOverride([InjuryEnum::MISSING_FINGER, InjuryEnum::BROKEN_FINGER, InjuryEnum::BURNT_HAND])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($mashedHand);

        $burntArms = new DiseaseConfig();
        $burntArms
            ->setDiseaseName(InjuryEnum::BURNT_ARMS)
            ->setType(MedicalConditionTypeEnum::INJURY)
            ->setModifierConfigs([
                $notMoveAction2Increase,
                $increaseCycleDiseaseChances10,
                $shootAction20PercentAccuracyLost,
            ])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($burntArms);

        $bustedArmJoint = new DiseaseConfig();
        $bustedArmJoint
            ->setDiseaseName(InjuryEnum::BUSTED_ARM_JOINT)
            ->setType(MedicalConditionTypeEnum::INJURY)
            ->setModifierConfigs([
                $notMoveAction2Increase,
                $shootAction40PercentAccuracyLost,
            ])
            ->setOverride([InjuryEnum::MASHED_ARMS])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($bustedArmJoint);

        $mashedArms = new DiseaseConfig();
        $mashedArms
            ->setDiseaseName(InjuryEnum::MASHED_ARMS)
            ->setType(MedicalConditionTypeEnum::INJURY)
            ->setModifierConfigs([
                $notMoveAction3Increase,
                $shootAction40PercentAccuracyLost,
            ])
            ->setOverride([
                InjuryEnum::BURNT_HAND,
                InjuryEnum::BROKEN_FINGER,
                InjuryEnum::MISSING_FINGER,
                InjuryEnum::MASHED_HAND,
                InjuryEnum::BUSTED_ARM_JOINT,
            ])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($mashedArms);

        $bruisedShoulder = new DiseaseConfig();
        $bruisedShoulder
            ->setDiseaseName(InjuryEnum::BRUISED_SHOULDER)
            ->setType(MedicalConditionTypeEnum::INJURY)
            ->setModifierConfigs([
                $reduceMax1HealthPoint,
                $shootAction10PercentAccuracyLost,
            ])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($bruisedShoulder);

        $brokenShoulder = new DiseaseConfig();
        $brokenShoulder
            ->setDiseaseName(InjuryEnum::BROKEN_SHOULDER)
            ->setType(MedicalConditionTypeEnum::INJURY)
            ->setModifierConfigs([
                $reduceMax1HealthPoint,
                $shootAction20PercentAccuracyLost,
                $cantPickUpHeavyItems,
            ])
            ->setOverride([InjuryEnum::BRUISED_SHOULDER])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($brokenShoulder);

        $bustedShoulder = new DiseaseConfig();
        $bustedShoulder
            ->setDiseaseName(InjuryEnum::BUSTED_SHOULDER)
            ->setType(MedicalConditionTypeEnum::INJURY)
            ->setModifierConfigs([
                $shootAction40PercentAccuracyLost,
                $notMoveAction2Increase,
                $cantPickUpHeavyItems,
            ])
            ->setOverride([
                InjuryEnum::BURNT_HAND,
                InjuryEnum::BROKEN_FINGER,
                InjuryEnum::MISSING_FINGER,
                InjuryEnum::MASHED_HAND,
                InjuryEnum::BUSTED_ARM_JOINT,
                InjuryEnum::MASHED_ARMS,
                InjuryEnum::BRUISED_SHOULDER,
                InjuryEnum::BROKEN_SHOULDER,
            ])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($bustedShoulder);

        $gameConfig
            ->addDiseaseConfig($brokenFinger)
            ->addDiseaseConfig($brokenFoot)
            ->addDiseaseConfig($brokenLeg)
            ->addDiseaseConfig($brokenRibs)
            ->addDiseaseConfig($bruisedShoulder)
            ->addDiseaseConfig($burns50OfBody)
            ->addDiseaseConfig($burns90OfBody)
            ->addDiseaseConfig($burntArms)
            ->addDiseaseConfig($burntHand)
            ->addDiseaseConfig($burstNose)
            ->addDiseaseConfig($bustedArmJoint)
            ->addDiseaseConfig($bustedShoulder)
            ->addDiseaseConfig($criticalHaemorrhage)
            ->addDiseaseConfig($haemorrhage)
            ->addDiseaseConfig($minorHaemorrhage)
            ->addDiseaseConfig($damagedEars)
            ->addDiseaseConfig($destroyedEars)
            ->addDiseaseConfig($disfunctionalLiver)
            ->addDiseaseConfig($headTrauma)
            ->addDiseaseConfig($implantedBullet)
            ->addDiseaseConfig($innerEarDamaged)
            ->addDiseaseConfig($mashedFoot)
            ->addDiseaseConfig($mashedHand)
            ->addDiseaseConfig($missingFinger)
            ->addDiseaseConfig($openAirBrain)
            ->addDiseaseConfig($puncturedLung)
            ->addDiseaseConfig($mashedArms)
            ->addDiseaseConfig($mashedLegs)
            ->addDiseaseConfig($tornTongue)
            ->addDiseaseConfig($brokenShoulder)
        ;
        $manager->persist($gameConfig);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            GameConfigFixtures::class,
            DiseaseModifierConfigFixtures::class,
            DisorderModifierConfigFixtures::class,
            InjuryModifierConfigFixtures::class,
            InjuryModifierConfigFixtures::class,
            InjuryModifierConfigFixtures::class,
            InjuryModifierConfigFixtures::class,
        ];
    }
}
