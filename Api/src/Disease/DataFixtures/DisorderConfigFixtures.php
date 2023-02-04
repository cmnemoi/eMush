<?php

namespace Mush\Disease\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Disease\Entity\Collection\SymptomConfigCollection;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Entity\Config\SymptomConfig;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Enum\DisorderEnum;
use Mush\Disease\Enum\TypeEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Modifier\DataFixtures\DiseaseModifierConfigFixtures;
use Mush\Modifier\DataFixtures\DisorderModifierConfigFixtures;
use Mush\Modifier\Entity\VariableEventModifierConfig;

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

        /** @var SymptomConfig $biting */
        $biting = $this->getReference(DiseaseSymptomConfigFixtures::BITING);
        /** @var SymptomConfig $fearOfCats */
        $fearOfCats = $this->getReference(DisorderSymptomConfigFixtures::FEAR_OF_CATS);
        /** @var SymptomConfig $noAttackActions */
        $noAttackActions = $this->getReference(DisorderSymptomConfigFixtures::NO_ATTACK_ACTIONS);
        /** @var SymptomConfig $noPilotingActions */
        $noPilotingActions = $this->getReference(DisorderSymptomConfigFixtures::NO_PILOTING_ACTIONS);
        /** @var SymptomConfig $noShootActions */
        $noShootActions = $this->getReference(DisorderSymptomConfigFixtures::NO_SHOOT_ACTIONS);
        /** @var SymptomConfig $psychoticAttacks */
        $psychoticAttacks = $this->getReference(DisorderSymptomConfigFixtures::PSYCHOTIC_ATTACKS);
        /** @var SymptomConfig $coprolaliaSymptom */
        $coprolaliaSymptom = $this->getReference(DisorderSymptomConfigFixtures::COPROLALIA_MESSAGES);
        /** @var SymptomConfig $paranoiaSymptom */
        $paranoiaSymptom = $this->getReference(DisorderSymptomConfigFixtures::PARANOIA_MESSAGES);

        $agoraphobia = new DiseaseConfig();
        $agoraphobia
            ->setDiseaseName(DisorderEnum::AGORAPHOBIA)
            ->setType(TypeEnum::DISORDER)
            ->setModifierConfigs(new ArrayCollection([
                $fourPeopleOneActionIncrease,
                $fourPeopleOneMovementIncrease,
            ]))
            ->setSymptomConfigs(new SymptomConfigCollection([
                $noPilotingActions,
            ]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($agoraphobia);

        $ailurophobia = new DiseaseConfig();
        $ailurophobia
            ->setDiseaseName(DisorderEnum::AILUROPHOBIA)
            ->setType(TypeEnum::DISORDER)
            ->setModifierConfigs(new ArrayCollection([
                $catInRoomMove2MovementIncrease,
                $catInRoomNotMove2ActionIncrease,
            ]))
            ->setSymptomConfigs(new SymptomConfigCollection([
                $fearOfCats,
            ]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($ailurophobia);

        $chronicMigraine = new DiseaseConfig();
        $chronicMigraine
            ->setDiseaseName(DisorderEnum::CHRONIC_MIGRAINE)
            ->setType(TypeEnum::DISORDER)
            ->setModifierConfigs(new ArrayCollection([
                $reduceMax2MoralPoint,
                $cycle1ActionLostRand16,
            ]))
            ->setOverride([DiseaseEnum::MIGRAINE])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($chronicMigraine);

        $chronicVertigo = new DiseaseConfig();
        $chronicVertigo
            ->setDiseaseName(DisorderEnum::CHRONIC_VERTIGO)
            ->setType(TypeEnum::DISORDER)
            ->setSymptomConfigs(new SymptomConfigCollection([
                $noPilotingActions,
            ]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($chronicVertigo);

        $coprolalia = new DiseaseConfig();
        $coprolalia
            ->setDiseaseName(DisorderEnum::COPROLALIA)
            ->setType(TypeEnum::DISORDER)
            ->setModifierConfigs(new ArrayCollection([
                $reduceMax4MoralPoint,
            ]))
            ->setSymptomConfigs(new SymptomConfigCollection([$coprolaliaSymptom]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($coprolalia);

        $crabism = new DiseaseConfig();
        $crabism
            ->setDiseaseName(DisorderEnum::CRABISM)
            ->setType(TypeEnum::DISORDER)
            ->setModifierConfigs(new ArrayCollection([
                $reduceMax4MoralPoint,
                $cycle1ActionLostRand16WithScreaming,
                $cycle1HealthLostRand16WithWallHeadBang,
                $cycle2MovementLostRand16WithRunInCircles,
            ]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($crabism);

        $depression = new DiseaseConfig();
        $depression
            ->setDiseaseName(DisorderEnum::DEPRESSION)
            ->setType(TypeEnum::DISORDER)
            ->setModifierConfigs(new ArrayCollection([
                $reduceMax2MoralPoint,
                $reduceMax2ActionPoint,
            ]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($depression);

        $paranoia = new DiseaseConfig();
        $paranoia
            ->setDiseaseName(DisorderEnum::PARANOIA)
            ->setType(TypeEnum::DISORDER)
            ->setModifierConfigs(new ArrayCollection([
                $reduceMax3MoralPoint,
            ]))
            ->setSymptomConfigs(new SymptomConfigCollection([$paranoiaSymptom]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($paranoia);

        $psychoticEpisode = new DiseaseConfig();
        $psychoticEpisode
            ->setDiseaseName(DisorderEnum::PSYCHOTIC_EPISODE)
            ->setType(TypeEnum::DISORDER)
            ->setSymptomConfigs(new SymptomConfigCollection([
                $biting,
                $psychoticAttacks,
            ]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($psychoticEpisode);

        $spleen = new DiseaseConfig();
        $spleen
            ->setDiseaseName(DisorderEnum::SPLEEN)
            ->setType(TypeEnum::DISORDER)
            ->setModifierConfigs(new ArrayCollection([
                $cycle1MoralLostRand70,
            ]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($spleen);

        $vertigo = new DiseaseConfig();
        $vertigo
            ->setDiseaseName(DisorderEnum::VERTIGO)
            ->setType(TypeEnum::DISORDER)
            ->setSymptomConfigs(new SymptomConfigCollection([
                $noPilotingActions,
            ]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($vertigo);

        $weaponPhobia = new DiseaseConfig();
        $weaponPhobia
            ->setDiseaseName(DisorderEnum::WEAPON_PHOBIA)
            ->setType(TypeEnum::DISORDER)
            ->setSymptomConfigs(new SymptomConfigCollection([
                $noAttackActions,
                $noShootActions,
            ]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
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
            ->addDiseaseConfig($weaponPhobia)
        ;
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
            DisorderSymptomConfigFixtures::class,
        ];
    }
}
