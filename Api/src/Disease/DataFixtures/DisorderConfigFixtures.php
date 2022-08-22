<?php

namespace Mush\Disease\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Disease\Entity\Collection\SymptomConfigCollection;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Entity\Config\SymptomConfig;
use Mush\Disease\Enum\DisorderEnum;
use Mush\Disease\Enum\TypeEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Modifier\DataFixtures\DiseaseModifierConfigFixtures;
use Mush\Modifier\DataFixtures\DisorderModifierConfigFixtures;
use Mush\Modifier\Entity\ModifierConfig;

class DisorderConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        /** @var ModifierConfig $catInRoomMove2MovementIncrease */
        $catInRoomMove2MovementIncrease = $this->getReference(DisorderModifierConfigFixtures::CAT_IN_ROOM_MOVE_2_MOVEMENT_INCREASE);
        /** @var ModifierConfig $catInRoomNotMove2ActionIncrease */
        $catInRoomNotMove2ActionIncrease = $this->getReference(DisorderModifierConfigFixtures::CAT_IN_ROOM_NOT_MOVE_2_ACTION_INCREASE);
        /** @var ModifierConfig $cycle1ActionLostRand16 */
        $cycle1ActionLostRand16 = $this->getReference(DiseaseModifierConfigFixtures::CYCLE_1_ACTION_LOST_RAND_16);
        /** @var ModifierConfig $cycle1ActionLostRand16WithScreaming */
        $cycle1ActionLostRand16WithScreaming = $this->getReference(DisorderModifierConfigFixtures::CYCLE_1_ACTION_LOST_RAND_16_WITH_SCREAMING);
        /** @var ModifierConfig $cycle1HealthLostRand16WithWallHeadBang */
        $cycle1HealthLostRand16WithWallHeadBang = $this->getReference(DisorderModifierConfigFixtures::CYCLE_1_HEALTH_LOST_RAND_16_WITH_WALL_HEAD_BANG);
        /** @var ModifierConfig $cycle1MoralLostRand70 */
        $cycle1MoralLostRand70 = $this->getReference(DisorderModifierConfigFixtures::CYCLE_1_MORAL_LOST_RAND_70);
        /** @var ModifierConfig $cycle2MovementLostRand16WithRunInCircles */
        $cycle2MovementLostRand16WithRunInCircles = $this->getReference(DisorderModifierConfigFixtures::CYCLE_2_MOVEMENT_LOST_RAND_16_WITH_RUN_IN_CIRCLES);
        /** @var ModifierConfig $fourPeopleOneActionIncrease */
        $fourPeopleOneActionIncrease = $this->getReference(DisorderModifierConfigFixtures::FOUR_PEOPLE_ONE_ACTION_INCREASE);
        /** @var ModifierConfig $fourPeopleOneMovementIncrease */
        $fourPeopleOneMovementIncrease = $this->getReference(DisorderModifierConfigFixtures::FOUR_PEOPLE_ONE_MOVEMENT_INCREASE);
        /** @var ModifierConfig $reduceMax2MoralPoint */
        $reduceMax2MoralPoint = $this->getReference(DiseaseModifierConfigFixtures::REDUCE_MAX_2_MORAL_POINT);
        /** @var ModifierConfig $reduceMax2ActionPoint */
        $reduceMax2ActionPoint = $this->getReference(DisorderModifierConfigFixtures::REDUCE_MAX_2_ACTION_POINT);
        /** @var ModifierConfig $reduceMax3MoralPoint */
        $reduceMax3MoralPoint = $this->getReference(DisorderModifierConfigFixtures::REDUCE_MAX_3_MORAL_POINT);
        /** @var ModifierConfig $reduceMax4MoralPoint */
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

        $agoraphobia = new DiseaseConfig();
        $agoraphobia
            ->setGameConfig($gameConfig)
            ->setName(DisorderEnum::AGORAPHOBIA)
            ->setType(TypeEnum::DISORDER)
            ->setModifierConfigs(new ArrayCollection([
                $fourPeopleOneActionIncrease,
                $fourPeopleOneMovementIncrease,
            ]))
            ->setSymptomConfigs(new SymptomConfigCollection([
                $noPilotingActions,
            ]))
        ;
        $manager->persist($agoraphobia);

        $ailurophobia = new DiseaseConfig();
        $ailurophobia
            ->setGameConfig($gameConfig)
            ->setName(DisorderEnum::AILUROPHOBIA)
            ->setType(TypeEnum::DISORDER)
            ->setModifierConfigs(new ArrayCollection([
                $catInRoomMove2MovementIncrease,
                $catInRoomNotMove2ActionIncrease,
            ]))
            ->setSymptomConfigs(new SymptomConfigCollection([
                $fearOfCats,
            ]));

        $manager->persist($ailurophobia);

        $chronicMigraine = new DiseaseConfig();
        $chronicMigraine
            ->setGameConfig($gameConfig)
            ->setName(DisorderEnum::CHRONIC_MIGRAINE)
            ->setType(TypeEnum::DISORDER)
            ->setModifierConfigs(new ArrayCollection([
                $reduceMax2MoralPoint,
                $cycle1ActionLostRand16,
            ]));

        $manager->persist($chronicMigraine);

        $chronicVertigo = new DiseaseConfig();
        $chronicVertigo
            ->setGameConfig($gameConfig)
            ->setName(DisorderEnum::CHRONIC_VERTIGO)
            ->setType(TypeEnum::DISORDER)
            ->setSymptomConfigs(new SymptomConfigCollection([
                $noPilotingActions,
            ]))
        ;

        $manager->persist($chronicVertigo);

        $coprolalia = new DiseaseConfig();
        $coprolalia
            ->setGameConfig($gameConfig)
            ->setName(DisorderEnum::COPROLALIA)
            ->setType(TypeEnum::DISORDER)
            ->setModifierConfigs(new ArrayCollection([
                $reduceMax4MoralPoint,
            ]));

        $manager->persist($coprolalia);

        $crabism = new DiseaseConfig();
        $crabism
            ->setGameConfig($gameConfig)
            ->setName(DisorderEnum::CRABISM)
            ->setType(TypeEnum::DISORDER)
            ->setModifierConfigs(new ArrayCollection([
                $reduceMax4MoralPoint,
                $cycle1ActionLostRand16WithScreaming,
                $cycle1HealthLostRand16WithWallHeadBang,
                $cycle2MovementLostRand16WithRunInCircles,
            ]));

        $manager->persist($crabism);

        $depression = new DiseaseConfig();
        $depression
            ->setGameConfig($gameConfig)
            ->setName(DisorderEnum::DEPRESSION)
            ->setType(TypeEnum::DISORDER)
            ->setModifierConfigs(new ArrayCollection([
                $reduceMax2MoralPoint,
                $reduceMax2ActionPoint,
            ]));

        $manager->persist($depression);

        $paranoia = new DiseaseConfig();
        $paranoia
            ->setGameConfig($gameConfig)
            ->setName(DisorderEnum::PARANOIA)
            ->setType(TypeEnum::DISORDER)
            ->setModifierConfigs(new ArrayCollection([
                $reduceMax3MoralPoint,
            ]));

        $manager->persist($paranoia);

        $psychoticEpisode = new DiseaseConfig();
        $psychoticEpisode
            ->setGameConfig($gameConfig)
            ->setName(DisorderEnum::PSYCHOTIC_EPISODE)
            ->setType(TypeEnum::DISORDER)
            ->setSymptomConfigs(new SymptomConfigCollection([
                $biting,
                $psychoticAttacks,
            ]));

        $manager->persist($psychoticEpisode);

        $spleen = new DiseaseConfig();
        $spleen
            ->setGameConfig($gameConfig)
            ->setName(DisorderEnum::SPLEEN)
            ->setType(TypeEnum::DISORDER)
            ->setModifierConfigs(new ArrayCollection([
                $cycle1MoralLostRand70,
            ]));

        $manager->persist($spleen);

        $vertigo = new DiseaseConfig();
        $vertigo
            ->setGameConfig($gameConfig)
            ->setName(DisorderEnum::VERTIGO)
            ->setType(TypeEnum::DISORDER)
            ->setSymptomConfigs(new SymptomConfigCollection([
                $noPilotingActions,
            ]));

        $manager->persist($vertigo);

        $weaponPhobia = new DiseaseConfig();
        $weaponPhobia
            ->setGameConfig($gameConfig)
            ->setName(DisorderEnum::WEAPON_PHOBIA)
            ->setType(TypeEnum::DISORDER)
            ->setSymptomConfigs(new SymptomConfigCollection([
                $noAttackActions,
                $noShootActions,
            ]));

        $manager->persist($weaponPhobia);

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
