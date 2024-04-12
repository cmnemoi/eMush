<?php

namespace Mush\Disease\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Enum\DisorderEnum;
use Mush\Disease\Enum\MedicalConditionTypeEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Modifier\DataFixtures\DiseaseModifierConfigFixtures;
use Mush\Modifier\DataFixtures\DisorderModifierConfigFixtures;
use Mush\Modifier\DataFixtures\InjuryModifierConfigFixtures;
use Mush\Modifier\Entity\Config\EventModifierConfig;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;

class DisorderConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        /** @var VariableEventModifierConfig $catInRoomMove2MovementIncrease */
        $catInRoomMove2MovementIncrease = $this->getReference(DisorderModifierConfigFixtures::CAT_IN_ROOM_MOVE_2_MOVEMENT_INCREASE);

        /** @var VariableEventModifierConfig $catInRoomNotMove2ActionIncrease */
        $catInRoomNotMove2ActionIncrease = $this->getReference(DisorderModifierConfigFixtures::CAT_IN_ROOM_NOT_MOVE_2_ACTION_INCREASE);

        /** @var VariableEventModifierConfig $cycle1ActionLostRand16 */
        $cycle1ActionLostRand16 = $this->getReference(DiseaseModifierConfigFixtures::CYCLE_1_ACTION_LOST_RAND_16);

        /** @var VariableEventModifierConfig $cycle1ActionLostRand16WithScreaming */
        $cycle1ActionLostRand16WithScreaming = $this->getReference(DisorderModifierConfigFixtures::CYCLE_1_ACTION_LOST_RAND_16_WITH_SCREAMING);

        /** @var VariableEventModifierConfig $cycle1HealthLostRand16WithWallHeadBang */
        $cycle1HealthLostRand16WithWallHeadBang = $this->getReference(DisorderModifierConfigFixtures::CYCLE_1_HEALTH_LOST_RAND_16_WITH_WALL_HEAD_BANG);

        /** @var VariableEventModifierConfig $cycle1MoralLostRand70 */
        $cycle1MoralLostRand70 = $this->getReference(DisorderModifierConfigFixtures::CYCLE_1_MORAL_LOST_RAND_70);

        /** @var VariableEventModifierConfig $cycle2MovementLostRand16WithRunInCircles */
        $cycle2MovementLostRand16WithRunInCircles = $this->getReference(DisorderModifierConfigFixtures::CYCLE_2_MOVEMENT_LOST_RAND_16_WITH_RUN_IN_CIRCLES);

        /** @var VariableEventModifierConfig $fourPeopleOneActionIncrease */
        $fourPeopleOneActionIncrease = $this->getReference(DisorderModifierConfigFixtures::FOUR_PEOPLE_ONE_ACTION_INCREASE);

        /** @var VariableEventModifierConfig $fourPeopleOneMovementIncrease */
        $fourPeopleOneMovementIncrease = $this->getReference(DisorderModifierConfigFixtures::FOUR_PEOPLE_ONE_MOVEMENT_INCREASE);

        /** @var VariableEventModifierConfig $reduceMax2MoralPoint */
        $reduceMax2MoralPoint = $this->getReference(DiseaseModifierConfigFixtures::REDUCE_MAX_2_MORAL_POINT);

        /** @var VariableEventModifierConfig $reduceMax2ActionPoint */
        $reduceMax2ActionPoint = $this->getReference(DisorderModifierConfigFixtures::REDUCE_MAX_2_ACTION_POINT);

        /** @var VariableEventModifierConfig $reduceMax3MoralPoint */
        $reduceMax3MoralPoint = $this->getReference(DisorderModifierConfigFixtures::REDUCE_MAX_3_MORAL_POINT);

        /** @var VariableEventModifierConfig $reduceMax4MoralPoint */
        $reduceMax4MoralPoint = $this->getReference(DisorderModifierConfigFixtures::REDUCE_MAX_4_MORAL_POINT);

        /** @var EventModifierConfig $coprolaliaSymptom */
        $coprolaliaSymptom = $this->getReference(InjuryModifierConfigFixtures::COPROLALIA_MODIFIER);

        /** @var EventModifierConfig $paranoiaSymptom */
        $paranoiaSymptom = $this->getReference(InjuryModifierConfigFixtures::PARANOIA_MODIFIER);

        /** @var EventModifierConfig $paranoiaDenial */
        $paranoiaDenial = $this->getReference(InjuryModifierConfigFixtures::PARANOIA_DENIAL_MODIFIER);

        /** @var EventModifierConfig $biting */
        $biting = $this->getReference(InjuryModifierConfigFixtures::BITING);

        /** @var EventModifierConfig $fearOfCats */
        $fearOfCats = $this->getReference(InjuryModifierConfigFixtures::FEAR_OF_CATS);

        /** @var EventModifierConfig $noAttackActions */
        $noAttackActions = $this->getReference(InjuryModifierConfigFixtures::PREVENT_ATTACK_ACTION);

        /** @var EventModifierConfig $noPilotingActions */
        $noPilotingActions = $this->getReference(InjuryModifierConfigFixtures::PREVENT_PILOTING);

        /** @var EventModifierConfig $noShootActions */
        $noShootActions = $this->getReference(InjuryModifierConfigFixtures::PREVENT_SHOOT_ACTION);

        /** @var EventModifierConfig $psychoticAttacks */
        $psychoticAttacks = $this->getReference(InjuryModifierConfigFixtures::PSYCHOTIC_ATTACKS);

        $agoraphobia = new DiseaseConfig();
        $agoraphobia
            ->setDiseaseName(DisorderEnum::AGORAPHOBIA)
            ->setType(MedicalConditionTypeEnum::DISORDER)
            ->setModifierConfigs([
                $fourPeopleOneActionIncrease,
                $fourPeopleOneMovementIncrease,
                $noPilotingActions,
            ])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($agoraphobia);

        $ailurophobia = new DiseaseConfig();
        $ailurophobia
            ->setDiseaseName(DisorderEnum::AILUROPHOBIA)
            ->setType(MedicalConditionTypeEnum::DISORDER)
            ->setModifierConfigs([
                $catInRoomMove2MovementIncrease,
                $catInRoomNotMove2ActionIncrease,
                $fearOfCats,
            ])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($ailurophobia);

        $chronicMigraine = new DiseaseConfig();
        $chronicMigraine
            ->setDiseaseName(DisorderEnum::CHRONIC_MIGRAINE)
            ->setType(MedicalConditionTypeEnum::DISORDER)
            ->setModifierConfigs([
                $reduceMax2MoralPoint,
                $cycle1ActionLostRand16,
            ])
            ->setOverride([DiseaseEnum::MIGRAINE])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($chronicMigraine);

        $chronicVertigo = new DiseaseConfig();
        $chronicVertigo
            ->setDiseaseName(DisorderEnum::CHRONIC_VERTIGO)
            ->setType(MedicalConditionTypeEnum::DISORDER)
            ->setModifierConfigs([
                $noPilotingActions,
            ])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($chronicVertigo);

        $coprolalia = new DiseaseConfig();
        $coprolalia
            ->setDiseaseName(DisorderEnum::COPROLALIA)
            ->setType(MedicalConditionTypeEnum::DISORDER)
            ->setModifierConfigs([
                $reduceMax4MoralPoint,
                $coprolaliaSymptom,
            ])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($coprolalia);

        $crabism = new DiseaseConfig();
        $crabism
            ->setDiseaseName(DisorderEnum::CRABISM)
            ->setType(MedicalConditionTypeEnum::DISORDER)
            ->setModifierConfigs([
                $reduceMax4MoralPoint,
                $cycle1ActionLostRand16WithScreaming,
                $cycle1HealthLostRand16WithWallHeadBang,
                $cycle2MovementLostRand16WithRunInCircles,
            ])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($crabism);

        $depression = new DiseaseConfig();
        $depression
            ->setDiseaseName(DisorderEnum::DEPRESSION)
            ->setType(MedicalConditionTypeEnum::DISORDER)
            ->setModifierConfigs([
                $reduceMax2MoralPoint,
                $reduceMax2ActionPoint,
            ])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($depression);

        $paranoia = new DiseaseConfig();
        $paranoia
            ->setDiseaseName(DisorderEnum::PARANOIA)
            ->setType(MedicalConditionTypeEnum::DISORDER)
            ->setModifierConfigs([
                $reduceMax3MoralPoint,
                $paranoiaSymptom,
                $paranoiaDenial,
            ])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($paranoia);

        $psychoticEpisode = new DiseaseConfig();
        $psychoticEpisode
            ->setDiseaseName(DisorderEnum::PSYCHOTIC_EPISODE)
            ->setType(MedicalConditionTypeEnum::DISORDER)
            ->setModifierConfigs([
                $biting,
                $psychoticAttacks,
            ])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($psychoticEpisode);

        $spleen = new DiseaseConfig();
        $spleen
            ->setDiseaseName(DisorderEnum::SPLEEN)
            ->setType(MedicalConditionTypeEnum::DISORDER)
            ->setModifierConfigs([
                $cycle1MoralLostRand70,
            ])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($spleen);

        $vertigo = new DiseaseConfig();
        $vertigo
            ->setDiseaseName(DisorderEnum::VERTIGO)
            ->setType(MedicalConditionTypeEnum::DISORDER)
            ->setModifierConfigs([
                $noPilotingActions,
            ])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($vertigo);

        $weaponPhobia = new DiseaseConfig();
        $weaponPhobia
            ->setDiseaseName(DisorderEnum::WEAPON_PHOBIA)
            ->setType(MedicalConditionTypeEnum::DISORDER)
            ->setModifierConfigs([
                $noAttackActions,
                $noShootActions,
            ])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($weaponPhobia);

        $gameConfig
            ->addDiseaseConfig($agoraphobia)
            ->addDiseaseConfig($ailurophobia)
            ->addDiseaseConfig($chronicMigraine)
            ->addDiseaseConfig($chronicVertigo)
            ->addDiseaseConfig($coprolalia)
            ->addDiseaseConfig($crabism)
            ->addDiseaseConfig($depression)
            ->addDiseaseConfig($paranoia)
            ->addDiseaseConfig($psychoticEpisode)
            ->addDiseaseConfig($spleen)
            ->addDiseaseConfig($vertigo)
            ->addDiseaseConfig($weaponPhobia);
        $manager->persist($gameConfig);

        $manager->flush();

        $this->addReference(DisorderEnum::AGORAPHOBIA, $agoraphobia);
        $this->addReference(DisorderEnum::AILUROPHOBIA, $ailurophobia);
        $this->addReference(DisorderEnum::CHRONIC_MIGRAINE, $chronicMigraine);
        $this->addReference(DisorderEnum::CHRONIC_VERTIGO, $chronicVertigo);
        $this->addReference(DisorderEnum::COPROLALIA, $coprolalia);
        $this->addReference(DisorderEnum::CRABISM, $crabism);
        $this->addReference(DisorderEnum::DEPRESSION, $depression);
        $this->addReference(DisorderEnum::PARANOIA, $paranoia);
        $this->addReference(DisorderEnum::PSYCHOTIC_EPISODE, $psychoticEpisode);
        $this->addReference(DisorderEnum::SPLEEN, $spleen);
        $this->addReference(DisorderEnum::VERTIGO, $vertigo);
        $this->addReference(DisorderEnum::WEAPON_PHOBIA, $weaponPhobia);
    }

    public function getDependencies()
    {
        return [
            GameConfigFixtures::class,
            DisorderModifierConfigFixtures::class,
            InjuryModifierConfigFixtures::class,
        ];
    }
}
