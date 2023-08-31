<?php

namespace Mush\Disease\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Disease\Entity\Collection\SymptomConfigCollection;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Entity\Config\SymptomConfig;
use Mush\Disease\Enum\InjuryEnum;
use Mush\Disease\Enum\TypeEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Modifier\DataFixtures\DiseaseModifierConfigFixtures;
use Mush\Modifier\DataFixtures\DisorderModifierConfigFixtures;
use Mush\Modifier\DataFixtures\InjuryModifierConfigFixtures;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;

class InjuryConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
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
            ->setDiseaseName(InjuryEnum::BURNS_50_OF_BODY)
            ->setType(TypeEnum::INJURY)
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
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs([
                $increaseCycleDiseaseChances10,
                ])
            ->setSymptomConfigs(new SymptomConfigCollection([
                $septicemiaOnCycleChange,
                $septicemiaOnDirtyEvent,
                $septicemiaOnPostAction,
            ]))
            ->setOverride([InjuryEnum::BURNS_50_OF_BODY])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($burns90OfBody);

        $burstNose = new DiseaseConfig();
        $burstNose
            ->setDiseaseName(InjuryEnum::BURST_NOSE)
            ->setType(TypeEnum::INJURY)
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
            ->setType(TypeEnum::INJURY)
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
            ->setType(TypeEnum::INJURY)
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
            ->setType(TypeEnum::INJURY)
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
            ->setType(TypeEnum::INJURY)
            ->setSymptomConfigs(new SymptomConfigCollection([
                $deaf,
            ]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($damagedEars);

        $destroyedEars = new DiseaseConfig();
        $destroyedEars
            ->setDiseaseName(InjuryEnum::DESTROYED_EARS)
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs([
                $reduceMax1MoralPoint,
            ])
            ->setSymptomConfigs(new SymptomConfigCollection([
                $deaf,
            ]))
            ->setOverride([InjuryEnum::DAMAGED_EARS])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($destroyedEars);

        $headTrauma = new DiseaseConfig();
        $headTrauma
            ->setDiseaseName(InjuryEnum::HEAD_TRAUMA)
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs([
                $reduceMax3MoralPoint,
            ])
            ->setSymptomConfigs(new SymptomConfigCollection([
                $septicemiaOnCycleChange,
                $septicemiaOnDirtyEvent,
                $septicemiaOnPostAction,
            ]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($headTrauma);

        $openAirBrain = new DiseaseConfig();
        $openAirBrain
            ->setDiseaseName(InjuryEnum::OPEN_AIR_BRAIN)
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs([
                $reduceMax2MoralPoint,
            ])
            ->setSymptomConfigs(new SymptomConfigCollection([
                $septicemiaOnCycleChange,
                $septicemiaOnDirtyEvent,
                $septicemiaOnPostAction,
            ]))
            ->setOverride([InjuryEnum::HEAD_TRAUMA])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($openAirBrain);

        $implantedBullet = new DiseaseConfig();
        $implantedBullet
            ->setDiseaseName(InjuryEnum::IMPLANTED_BULLET)
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs([
                $notMoveAction1Increase,
            ])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($implantedBullet);

        $innerEarDamaged = new DiseaseConfig();
        $innerEarDamaged
            ->setDiseaseName(InjuryEnum::INNER_EAR_DAMAGED)
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs([
                $notMoveAction1Increase,
                $moveIncreaseMovement,
            ])
            ->setSymptomConfigs(new SymptomConfigCollection([
                $noPilotingActions,
            ]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($innerEarDamaged);

        $tornTongue = new DiseaseConfig();
        $tornTongue
            ->setDiseaseName(InjuryEnum::TORN_TONGUE)
            ->setType(TypeEnum::INJURY)
            ->setSymptomConfigs(new SymptomConfigCollection([
                $mute,
            ]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($tornTongue);

        // foot and leg
        $brokenFoot = new DiseaseConfig();
        $brokenFoot
            ->setDiseaseName(InjuryEnum::BROKEN_FOOT)
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs([
                $moveIncreaseMovement,
            ])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($brokenFoot);

        $mashedFoot = new DiseaseConfig();
        $mashedFoot
            ->setDiseaseName(InjuryEnum::MASHED_FOOT)
            ->setType(TypeEnum::INJURY)
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
            ->setType(TypeEnum::INJURY)
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
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs([
                $reduceMax12MovementPoint,
            ])
            ->setSymptomConfigs(new SymptomConfigCollection([
                $cantMove,
            ]))
            ->setOverride([InjuryEnum::BROKEN_LEG, InjuryEnum::BROKEN_FOOT, InjuryEnum::MASHED_FOOT])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($mashedLegs);

        // Torso
        $disfunctionalLiver = new DiseaseConfig();
        $disfunctionalLiver
            ->setDiseaseName(InjuryEnum::DISFUNCTIONAL_LIVER)
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs([
                $notMoveAction1Increase,
                $consume2ActionLoss,
            ])
            ->setSymptomConfigs(new SymptomConfigCollection([
                $consumeVomiting,
                $drooling,
                $moveVomiting,
            ]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($disfunctionalLiver);

        $puncturedLung = new DiseaseConfig();
        $puncturedLung
            ->setDiseaseName(InjuryEnum::PUNCTURED_LUNG)
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs([
                $cycle2HealthLost,
                $notMoveAction2Increase,
            ])
            ->setSymptomConfigs(new SymptomConfigCollection([
                $mute,
            ]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($puncturedLung);

        $brokenRibs = new DiseaseConfig();
        $brokenRibs
            ->setDiseaseName(InjuryEnum::BROKEN_RIBS)
            ->setType(TypeEnum::INJURY)
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
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs([
                $notMoveAction1Increase,
            ])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($brokenFinger);

        $missingFinger = new DiseaseConfig();
        $missingFinger
            ->setDiseaseName(InjuryEnum::MISSING_FINGER)
            ->setType(TypeEnum::INJURY)
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
            ->setType(TypeEnum::INJURY)
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
            ->setType(TypeEnum::INJURY)
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
            ->setType(TypeEnum::INJURY)
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
            ->setType(TypeEnum::INJURY)
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
            ->setType(TypeEnum::INJURY)
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
            ->setType(TypeEnum::INJURY)
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
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs([
                $reduceMax1HealthPoint,
                $shootAction20PercentAccuracyLost,
            ])
            ->setSymptomConfigs(new SymptomConfigCollection([$cantPickUpHeavyItems]))
            ->setOverride([InjuryEnum::BRUISED_SHOULDER])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($brokenShoulder);

        $bustedShoulder = new DiseaseConfig();
        $bustedShoulder
            ->setDiseaseName(InjuryEnum::BUSTED_SHOULDER)
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs([
                $shootAction40PercentAccuracyLost,
                $notMoveAction2Increase,
            ])
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
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($bustedShoulder);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            DiseaseModifierConfigFixtures::class,
            DiseaseSymptomConfigFixtures::class,
            DisorderSymptomConfigFixtures::class,
            InjuryModifierConfigFixtures::class,
            InjurySymptomConfigFixtures::class,
        ];
    }
}
