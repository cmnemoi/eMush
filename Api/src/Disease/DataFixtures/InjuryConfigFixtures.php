<?php

namespace Mush\Disease\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Disease\Entity\Collection\SymptomConfigCollection;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Entity\Config\SymptomConfig;
use Mush\Disease\Enum\InjuryEnum;
use Mush\Disease\Enum\TypeEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Modifier\DataFixtures\DiseaseModifierConfigFixtures;
use Mush\Modifier\DataFixtures\DisorderModifierConfigFixtures;
use Mush\Modifier\DataFixtures\InjuryModifierConfigFixtures;
use Mush\Modifier\Entity\ModifierConfig;

class InjuryConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        /** @var ModifierConfig $consume2ActionLoss */
        $consume2ActionLoss = $this->getReference(DiseaseModifierConfigFixtures::CONSUME_2_ACTION_LOSS);
        /** @var ModifierConfig $cycle1HealthLoss */
        $cycle1HealthLost = $this->getReference(DiseaseModifierConfigFixtures::CYCLE_1_HEALTH_LOST);
        /** @var ModifierConfig $cycle2HealthLoss */
        $cycle2HealthLost = $this->getReference(DiseaseModifierConfigFixtures::CYCLE_2_HEALTH_LOST);
        /** @var ModifierConfig $increaseCycleDiseaseChances10 */
        $increaseCycleDiseaseChances10 = $this->getReference(DiseaseModifierConfigFixtures::INCREASE_CYCLE_DISEASE_CHANCES_10);
        /** @var ModifierConfig $moveIncreaseMovement */
        $moveIncreaseMovement = $this->getReference(DiseaseModifierConfigFixtures::MOVE_INCREASE_MOVEMENT);
        /** @var ModifierConfig $notMoveAction1Increase */
        $notMoveAction1Increase = $this->getReference(InjuryModifierConfigFixtures::NOT_MOVE_ACTION_1_INCREASE);
        /** @var ModifierConfig $notMoveAction2Increase */
        $notMoveAction2Increase = $this->getReference(InjuryModifierConfigFixtures::NOT_MOVE_ACTION_2_INCREASE);
        /** @var ModifierConfig $notMoveAction3Increase */
        $notMoveAction3Increase = $this->getReference(InjuryModifierConfigFixtures::NOT_MOVE_ACTION_3_INCREASE);
        /** @var ModifierConfig $reduceMax1HealthPoint */
        $reduceMax1HealthPoint = $this->getReference(DiseaseModifierConfigFixtures::REDUCE_MAX_1_HEALTH_POINT);
        /** @var ModifierConfig $reduceMax2HealthPoint */
        $reduceMax2HealthPoint = $this->getReference(DiseaseModifierConfigFixtures::REDUCE_MAX_2_HEALTH_POINT);
        /** @var ModifierConfig $reduceMax1MoralPoint */
        $reduceMax1MoralPoint = $this->getReference(DiseaseModifierConfigFixtures::REDUCE_MAX_1_MORAL_POINT);
        /** @var ModifierConfig $reduceMax2MoralPoint */
        $reduceMax2MoralPoint = $this->getReference(DiseaseModifierConfigFixtures::REDUCE_MAX_2_MORAL_POINT);
        /** @var ModifierConfig $reduceMax3MoralPoint */
        $reduceMax3MoralPoint = $this->getReference(DisorderModifierConfigFixtures::REDUCE_MAX_3_MORAL_POINT);
        /** @var ModifierConfig $reduce3MaxMovementPoint */
        $reduceMax3MovementPoint = $this->getReference(InjuryModifierConfigFixtures::REDUCE_MAX_3_MOVEMENT_POINT);
        /** @var ModifierConfig $reduce5MaxMovementPoint */
        $reduceMax5MovementPoint = $this->getReference(InjuryModifierConfigFixtures::REDUCE_MAX_5_MOVEMENT_POINT);
        /** @var ModifierConfig $reduce12MaxMovementPoint */
        $reduceMax12MovementPoint = $this->getReference(InjuryModifierConfigFixtures::REDUCE_MAX_12_MOVEMENT_POINT);
        /** @var ModifierConfig $shootAction10PercentAccuracyLost */
        $shootAction10PercentAccuracyLost = $this->getReference(DiseaseModifierConfigFixtures::SHOOT_ACTION_10_PERCENT_ACCURACY_LOST);
        /** @var ModifierConfig $shootAction20PercentAccuracyLost */
        $shootAction20PercentAccuracyLost = $this->getReference(InjuryModifierConfigFixtures::SHOOT_ACTION_20_PERCENT_ACCURACY_LOST);
        /** @var ModifierConfig $shootAction40PercentAccuracyLost */
        $shootAction40PercentAccuracyLost = $this->getReference(InjuryModifierConfigFixtures::SHOOT_ACTION_40_PERCENT_ACCURACY_LOST);

        /** @var SymptomConfig $cantMove */
        $cantMove = $this->getReference(InjurySymptomConfigFixtures::CANT_MOVE);
        /** @var SymptomConfig $cantPickUpHeavyItems */
        $cantPickUpHeavyItems = $this->getReference(InjurySymptomConfigFixtures::CANT_PICK_UP_HEAVY_ITEMS);
        /** @var SymptomConfig $consumeVomiting */
        $consumeVomiting = $this->getReference(DiseaseSymptomConfigFixtures::CONSUME_VOMITING);
        /** @var SymptomConfig $deaf */
        $deaf = $this->getReference(InjurySymptomConfigFixtures::DEAF);
        /** @var SymptomConfig $drooling */
        $drooling = $this->getReference(DiseaseSymptomConfigFixtures::DROOLING);
        /** @var SymptomConfig $moveVomiting */
        $moveVomiting = $this->getReference(DiseaseSymptomConfigFixtures::MOVE_VOMITING);
        /** @var SymptomConfig $mute */
        $mute = $this->getReference(InjurySymptomConfigFixtures::MUTE);
        /** @var SymptomConfig $noPilotingActions */
        $noPilotingActions = $this->getReference(DisorderSymptomConfigFixtures::NO_PILOTING_ACTIONS);
        /** @var SymptomConfig $septicemiaOnCycleChange */
        $septicemiaOnCycleChange = $this->getReference(InjurySymptomConfigFixtures::SEPTICEMIA_ON_CYCLE_CHANGE);
        /** @var SymptomConfig $septicemiaOnDirtyEvent */
        $septicemiaOnDirtyEvent = $this->getReference(InjurySymptomConfigFixtures::SEPTICEMIA_ON_DIRTY_EVENT);
        /** @var SymptomConfig $septicemiaOnPostAction */
        $septicemiaOnPostAction = $this->getReference(InjurySymptomConfigFixtures::SEPTICEMIA_ON_POST_ACTION);

        // burn
        $burns50OfBody = new DiseaseConfig();
        $burns50OfBody
            ->setName(InjuryEnum::BURNS_50_OF_BODY)
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs(new ArrayCollection([
                $notMoveAction1Increase,
                $increaseCycleDiseaseChances10,
                ]))
        ;
        $manager->persist($burns50OfBody);

        $burns90OfBody = new DiseaseConfig();
        $burns90OfBody
            ->setName(InjuryEnum::BURNS_90_OF_BODY)
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs(new ArrayCollection([
                $increaseCycleDiseaseChances10,
                ]))
            ->setSymptomConfigs(new SymptomConfigCollection([
                $septicemiaOnCycleChange,
                $septicemiaOnDirtyEvent,
                $septicemiaOnPostAction,
            ]))
            ->setOverride([InjuryEnum::BURNS_50_OF_BODY])
        ;
        $manager->persist($burns90OfBody);

        $burstNose = new DiseaseConfig();
        $burstNose
            ->setName(InjuryEnum::BURST_NOSE)
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs(new ArrayCollection([
                $reduceMax2MoralPoint,
            ]))
        ;
        $manager->persist($burstNose);

        // Haemorrhage
        $criticalHaemorrhage = new DiseaseConfig();
        $criticalHaemorrhage
            ->setName(InjuryEnum::CRITICAL_HAEMORRHAGE)
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs(new ArrayCollection([
                $cycle2HealthLost,
                $reduceMax2HealthPoint,
            ]))
        ;
        $manager->persist($criticalHaemorrhage);

        $haemorrhage = new DiseaseConfig();
        $haemorrhage
            ->setName(InjuryEnum::HAEMORRHAGE)
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs(new ArrayCollection([
                $cycle1HealthLost,
                $reduceMax1HealthPoint,
            ]))
        ;
        $manager->persist($haemorrhage);

        $minorHaemorrhage = new DiseaseConfig();
        $minorHaemorrhage
            ->setName(InjuryEnum::MINOR_HAEMORRHAGE)
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs(new ArrayCollection([
                $cycle1HealthLost,
            ]))
        ;
        $manager->persist($minorHaemorrhage);

        // Ears
        $damagedEars = new DiseaseConfig();
        $damagedEars
            ->setName(InjuryEnum::DAMAGED_EARS)
            ->setType(TypeEnum::INJURY)
            ->setSymptomConfigs(new SymptomConfigCollection([
                $deaf,
            ]))
        ;
        $manager->persist($damagedEars);

        $destroyedEars = new DiseaseConfig();
        $destroyedEars
            ->setName(InjuryEnum::DESTROYED_EARS)
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs(new ArrayCollection([
                $reduceMax1MoralPoint,
            ]))
            ->setSymptomConfigs(new SymptomConfigCollection([
                $deaf,
            ]))
            ->setOverride([InjuryEnum::DAMAGED_EARS])
        ;
        $manager->persist($destroyedEars);

        $headTrauma = new DiseaseConfig();
        $headTrauma
            ->setName(InjuryEnum::HEAD_TRAUMA)
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs(new ArrayCollection([
                $reduceMax3MoralPoint,
            ]))
            ->setSymptomConfigs(new SymptomConfigCollection([
                $septicemiaOnCycleChange,
                $septicemiaOnDirtyEvent,
                $septicemiaOnPostAction,
            ]))
        ;
        $manager->persist($headTrauma);

        $openAirBrain = new DiseaseConfig();
        $openAirBrain
            ->setName(InjuryEnum::OPEN_AIR_BRAIN)
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs(new ArrayCollection([
                $reduceMax2MoralPoint,
            ]))
            ->setSymptomConfigs(new SymptomConfigCollection([
                $septicemiaOnCycleChange,
                $septicemiaOnDirtyEvent,
                $septicemiaOnPostAction,
            ]))
            ->setOverride([InjuryEnum::HEAD_TRAUMA])
        ;
        $manager->persist($openAirBrain);

        $implantedBullet = new DiseaseConfig();
        $implantedBullet
            ->setName(InjuryEnum::IMPLANTED_BULLET)
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs(new ArrayCollection([
                $notMoveAction1Increase,
            ]))
        ;
        $manager->persist($implantedBullet);

        $innerEarDamaged = new DiseaseConfig();
        $innerEarDamaged
            ->setName(InjuryEnum::INNER_EAR_DAMAGED)
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs(new ArrayCollection([
                $notMoveAction1Increase,
                $moveIncreaseMovement,
            ]))
            ->setSymptomConfigs(new SymptomConfigCollection([
                $noPilotingActions,
            ]))
        ;
        $manager->persist($innerEarDamaged);

        $tornTongue = new DiseaseConfig();
        $tornTongue
            ->setName(InjuryEnum::TORN_TONGUE)
            ->setType(TypeEnum::INJURY)
            ->setSymptomConfigs(new SymptomConfigCollection([
                $mute,
            ]))
        ;
        $manager->persist($tornTongue);

        // foot and leg
        $brokenFoot = new DiseaseConfig();
        $brokenFoot
            ->setName(InjuryEnum::BROKEN_FOOT)
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs(new ArrayCollection([
                $moveIncreaseMovement,
            ]))
        ;
        $manager->persist($brokenFoot);

        $mashedFoot = new DiseaseConfig();
        $mashedFoot
            ->setName(InjuryEnum::MASHED_FOOT)
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs(new ArrayCollection([
                $moveIncreaseMovement,
                $reduceMax3MovementPoint,
            ]))
            ->setOverride([InjuryEnum::BROKEN_FOOT])
        ;
        $manager->persist($mashedFoot);

        $brokenLeg = new DiseaseConfig();
        $brokenLeg
            ->setName(InjuryEnum::BROKEN_LEG)
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs(new ArrayCollection([
                $reduceMax5MovementPoint,
                $moveIncreaseMovement,
            ]))
        ;
        $manager->persist($brokenLeg);

        $mashedLegs = new DiseaseConfig();
        $mashedLegs
            ->setName(InjuryEnum::MASHED_LEGS)
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs(new ArrayCollection([
                $reduceMax12MovementPoint,
            ]))
            ->setSymptomConfigs(new SymptomConfigCollection([
                $cantMove,
            ]))
            ->setOverride([InjuryEnum::BROKEN_LEG, InjuryEnum::BROKEN_FOOT, InjuryEnum::MASHED_FOOT])
        ;
        $manager->persist($mashedLegs);

        // Torso
        $disfunctionalLiver = new DiseaseConfig();
        $disfunctionalLiver
            ->setName(InjuryEnum::DISFUNCTIONAL_LIVER)
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs(new ArrayCollection([
                $notMoveAction1Increase,
                $consume2ActionLoss,
            ]))
            ->setSymptomConfigs(new SymptomConfigCollection([
                $consumeVomiting,
                $drooling,
                $moveVomiting,
            ]))
        ;
        $manager->persist($disfunctionalLiver);

        $puncturedLung = new DiseaseConfig();
        $puncturedLung
            ->setName(InjuryEnum::PUNCTURED_LUNG)
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs(new ArrayCollection([
                $cycle2HealthLost,
                $notMoveAction2Increase,
            ]))
            ->setSymptomConfigs(new SymptomConfigCollection([
                $mute,
            ]))
        ;
        $manager->persist($puncturedLung);

        $brokenRibs = new DiseaseConfig();
        $brokenRibs
            ->setName(InjuryEnum::BROKEN_RIBS)
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs(new ArrayCollection([
                $shootAction20PercentAccuracyLost,
            ]))
        ;
        $manager->persist($brokenRibs);

        // finger, arm and shoulder
        $brokenFinger = new DiseaseConfig();
        $brokenFinger
            ->setName(InjuryEnum::BROKEN_FINGER)
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs(new ArrayCollection([
                $notMoveAction1Increase,
            ]))
        ;
        $manager->persist($brokenFinger);

        $missingFinger = new DiseaseConfig();
        $missingFinger
            ->setName(InjuryEnum::MISSING_FINGER)
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs(new ArrayCollection([
                $notMoveAction1Increase,
            ]))
            ->setOverride([InjuryEnum::BROKEN_FINGER])
        ;
        $manager->persist($missingFinger);

        $burntHand = new DiseaseConfig();
        $burntHand
            ->setName(InjuryEnum::BURNT_HAND)
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs(new ArrayCollection([
                $notMoveAction1Increase,
                $reduceMax1HealthPoint,
            ]))
        ;
        $manager->persist($burntHand);

        $mashedHand = new DiseaseConfig();
        $mashedHand
            ->setName(InjuryEnum::MASHED_HAND)
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs(new ArrayCollection([
                $notMoveAction2Increase,
                $shootAction40PercentAccuracyLost,
            ]))
            ->setOverride([InjuryEnum::MISSING_FINGER, InjuryEnum::BROKEN_FINGER, InjuryEnum::BURNT_HAND])
        ;
        $manager->persist($mashedHand);

        $burntArms = new DiseaseConfig();
        $burntArms
            ->setName(InjuryEnum::BURNT_ARMS)
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs(new ArrayCollection([
                $notMoveAction2Increase,
                $increaseCycleDiseaseChances10,
                $shootAction20PercentAccuracyLost,
            ]))
        ;
        $manager->persist($burntArms);

        $bustedArmJoint = new DiseaseConfig();
        $bustedArmJoint
            ->setName(InjuryEnum::BUSTED_ARM_JOINT)
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs(new ArrayCollection([
                $notMoveAction2Increase,
                $shootAction40PercentAccuracyLost,
            ]))
            ->setOverride([InjuryEnum::MASHED_ARMS])
        ;
        $manager->persist($bustedArmJoint);

        $mashedArms = new DiseaseConfig();
        $mashedArms
            ->setName(InjuryEnum::MASHED_ARMS)
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs(new ArrayCollection([
                $notMoveAction3Increase,
                $shootAction40PercentAccuracyLost,
            ]))
            ->setOverride([
                InjuryEnum::BURNT_HAND,
                InjuryEnum::BROKEN_FINGER,
                InjuryEnum::MISSING_FINGER,
                InjuryEnum::MASHED_HAND,
                InjuryEnum::BUSTED_ARM_JOINT,
            ])
        ;
        $manager->persist($mashedArms);

        $bruisedShoulder = new DiseaseConfig();
        $bruisedShoulder
            ->setName(InjuryEnum::BRUISED_SHOULDER)
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs(new ArrayCollection([
                $reduceMax1HealthPoint,
                $shootAction10PercentAccuracyLost,
            ]))
        ;
        $manager->persist($bruisedShoulder);

        $brokenShoulder = new DiseaseConfig();
        $brokenShoulder
            ->setName(InjuryEnum::BROKEN_SHOULDER)
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs(new ArrayCollection([
                $reduceMax1HealthPoint,
                $shootAction20PercentAccuracyLost,
            ]))
            ->setSymptomConfigs(new SymptomConfigCollection([$cantPickUpHeavyItems]))
            ->setOverride([InjuryEnum::BRUISED_SHOULDER])
        ;
        $manager->persist($brokenShoulder);

        $bustedShoulder = new DiseaseConfig();
        $bustedShoulder
            ->setName(InjuryEnum::BUSTED_SHOULDER)
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs(new ArrayCollection([
                $shootAction40PercentAccuracyLost,
                $notMoveAction2Increase,
            ]))
            ->setSymptomConfigs(new SymptomConfigCollection([
                $cantPickUpHeavyItems,
            ]))
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
            DiseaseSymptomConfigFixtures::class,
            DisorderSymptomConfigFixtures::class,
            InjuryModifierConfigFixtures::class,
            InjurySymptomConfigFixtures::class,
        ];
    }
}
