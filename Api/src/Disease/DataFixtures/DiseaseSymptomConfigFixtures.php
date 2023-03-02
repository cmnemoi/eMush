<?php

namespace Mush\Disease\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Disease\Entity\Config\SymptomActivationRequirement;
use Mush\Disease\Entity\Config\SymptomConfig;
use Mush\Disease\Enum\SymptomActivationRequirementEnum;
use Mush\Disease\Enum\SymptomEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Status\Enum\PlayerStatusEnum;

class DiseaseSymptomConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public const BITING = 'biting';
    public const BREAKOUTS = 'breakouts';
    public const CAT_SNEEZING = 'cat_sneezing';
    public const CAT_ALLERGY_SYMPTOM = 'cat_allergy_symptom';
    public const CONSUME_DRUG_VOMITING = 'consume_drug_vomiting';
    public const CONSUME_VOMITING = 'consume_vomiting';
    public const CYCLE_DIRTINESS = 'cycle_dirtiness';
    public const CYCLE_DIRTINESS_RAND_40 = 'cycle_dirtiness_rand_40';
    public const DROOLING = 'drooling';
    public const FOAMING_MOUTH = 'foaming_mouth';
    public const MOVE_VOMITING = 'move_vomiting';
    public const MUSH_SNEEZING = 'mush_sneezing';
    public const ITEM_IN_ROOM_CAT = 'item_in_room_cat';
    public const REASON_MOVE = 'reason_move';
    public const RANDOM_16 = 'random_16';

    public function load(ObjectManager $manager): void
    {
        $catIsInRoomActivationRequirement = new SymptomActivationRequirement(SymptomActivationRequirementEnum::ITEM_IN_ROOM);
        $catIsInRoomActivationRequirement
            ->setActivationRequirement(ItemEnum::SCHRODINGER)
            ->buildName()
        ;
        $manager->persist($catIsInRoomActivationRequirement);

        $consumeActionActivationRequirement = new SymptomActivationRequirement(SymptomActivationRequirementEnum::REASON);
        $consumeActionActivationRequirement
            ->setActivationRequirement(ActionEnum::CONSUME)
            ->buildName()
        ;
        $manager->persist($consumeActionActivationRequirement);

        $consumeDrugActionActivationRequirement = new SymptomActivationRequirement(SymptomActivationRequirementEnum::REASON);
        $consumeDrugActionActivationRequirement
            ->setActivationRequirement(ActionEnum::CONSUME_DRUG)
            ->buildName()
        ;
        $manager->persist($consumeDrugActionActivationRequirement);

        $holdCatActivationRequirement = new SymptomActivationRequirement(SymptomActivationRequirementEnum::PLAYER_EQUIPMENT);
        $holdCatActivationRequirement
            ->setActivationRequirement(ItemEnum::SCHRODINGER)
            ->buildName()
        ;
        $manager->persist($holdCatActivationRequirement);

        $moveActionActivationRequirement = new SymptomActivationRequirement(SymptomActivationRequirementEnum::REASON);
        $moveActionActivationRequirement
            ->setActivationRequirement(ActionEnum::MOVE)
            ->buildName()
        ;
        $manager->persist($moveActionActivationRequirement);

        $mushIsInRoomActivationRequirement = new SymptomActivationRequirement(SymptomActivationRequirementEnum::PLAYER_IN_ROOM);
        $mushIsInRoomActivationRequirement
            ->setActivationRequirement(SymptomActivationRequirementEnum::MUSH_IN_ROOM)
            ->buildName()
        ;
        $manager->persist($mushIsInRoomActivationRequirement);

        $notAloneActivationRequirement = new SymptomActivationRequirement(SymptomActivationRequirementEnum::PLAYER_IN_ROOM);
        $notAloneActivationRequirement
            ->setActivationRequirement(SymptomActivationRequirementEnum::NOT_ALONE)
            ->buildName()
        ;
        $manager->persist($notAloneActivationRequirement);

        $randActivationRequirement16 = new SymptomActivationRequirement(SymptomActivationRequirementEnum::RANDOM);
        $randActivationRequirement16
            ->setValue(16)
            ->buildName()
        ;
        $manager->persist($randActivationRequirement16);

        $randActivationRequirement40 = new SymptomActivationRequirement(SymptomActivationRequirementEnum::RANDOM);
        $randActivationRequirement40
            ->setValue(40)
            ->buildName()
        ;
        $manager->persist($randActivationRequirement40);

        $takeActionActivationRequirement = new SymptomActivationRequirement(SymptomActivationRequirementEnum::REASON);
        $takeActionActivationRequirement
            ->setActivationRequirement(ActionEnum::TAKE)
            ->buildName()
        ;
        $manager->persist($takeActionActivationRequirement);

        $biting = new SymptomConfig(SymptomEnum::BITING);
        $biting
            ->setTrigger(EventEnum::NEW_CYCLE)
            ->addSymptomActivationRequirement($notAloneActivationRequirement)
            ->addSymptomActivationRequirement($randActivationRequirement16)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($biting);

        $breakouts = new SymptomConfig(SymptomEnum::BREAKOUTS);
        $breakouts
            ->setTrigger(ActionEvent::POST_ACTION)
            ->addSymptomActivationRequirement($randActivationRequirement16)
            ->addSymptomActivationRequirement($moveActionActivationRequirement)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($breakouts);

        $catAllergySymptom = new SymptomConfig(SymptomEnum::CAT_ALLERGY);
        $catAllergySymptom
            ->setTrigger(ActionEvent::POST_ACTION)
            ->addSymptomActivationRequirement($holdCatActivationRequirement)
            ->addSymptomActivationRequirement($takeActionActivationRequirement)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($catAllergySymptom);

        $catSneezing = new SymptomConfig(SymptomEnum::SNEEZING);
        $catSneezing
            ->setTrigger(ActionEvent::POST_ACTION)
            ->addSymptomActivationRequirement($moveActionActivationRequirement)
            ->addSymptomActivationRequirement($catIsInRoomActivationRequirement)
            ->buildName(GameConfigEnum::DEFAULT, 'cat')
        ;
        $manager->persist($catSneezing);

        $consumeDrugVomiting = new SymptomConfig(SymptomEnum::VOMITING);
        $consumeDrugVomiting
            ->setTrigger(ActionEvent::POST_ACTION)
            ->setVisibility(VisibilityEnum::SECRET)
            ->addSymptomActivationRequirement($consumeDrugActionActivationRequirement)
            ->buildName(GameConfigEnum::DEFAULT, ActionEnum::CONSUME_DRUG)
        ;
        $manager->persist($consumeDrugVomiting);

        $consumeVomiting = new SymptomConfig(SymptomEnum::VOMITING);
        $consumeVomiting
            ->setTrigger(ActionEvent::POST_ACTION)
            ->setVisibility(VisibilityEnum::SECRET)
            ->addSymptomActivationRequirement($consumeActionActivationRequirement)
            ->buildName(GameConfigEnum::DEFAULT, ActionEnum::CONSUME)
        ;
        $manager->persist($consumeVomiting);

        $cycleDirtiness = new SymptomConfig(SymptomEnum::DIRTINESS);
        $cycleDirtiness
            ->setTrigger(EventEnum::NEW_CYCLE)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($cycleDirtiness);

        $cycleDirtinessRand40 = new SymptomConfig(SymptomEnum::DIRTINESS);
        $cycleDirtinessRand40
            ->setTrigger(EventEnum::NEW_CYCLE)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->addSymptomActivationRequirement($randActivationRequirement40)
            ->buildName(GameConfigEnum::DEFAULT, 'random_40')
        ;
        $manager->persist($cycleDirtinessRand40);

        $drooling = new SymptomConfig(SymptomEnum::DROOLING);
        $drooling
            ->setTrigger(ActionEvent::POST_ACTION)
            ->addSymptomActivationRequirement($randActivationRequirement16)
            ->addSymptomActivationRequirement($moveActionActivationRequirement)
            ->buildName(GameConfigEnum::DEFAULT)

        ;
        $manager->persist($drooling);

        $foamingMouth = new SymptomConfig(SymptomEnum::FOAMING_MOUTH);
        $foamingMouth
            ->setTrigger(ActionEvent::POST_ACTION)
            ->addSymptomActivationRequirement($randActivationRequirement16)
            ->addSymptomActivationRequirement($moveActionActivationRequirement)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($foamingMouth);

        $moveVomiting = new SymptomConfig(SymptomEnum::VOMITING);
        $moveVomiting
            ->setTrigger(ActionEvent::POST_ACTION)
            ->addSymptomActivationRequirement($moveActionActivationRequirement)
            ->addSymptomActivationRequirement($randActivationRequirement40)
            ->buildName(GameConfigEnum::DEFAULT, ActionEnum::MOVE)
        ;
        $manager->persist($moveVomiting);

        $mushSneezing = new SymptomConfig(SymptomEnum::SNEEZING);
        $mushSneezing
            ->setTrigger(ActionEvent::POST_ACTION)
            ->addSymptomActivationRequirement($mushIsInRoomActivationRequirement)
            ->addSymptomActivationRequirement($moveActionActivationRequirement)
            ->addSymptomActivationRequirement($randActivationRequirement16)
            ->buildName(GameConfigEnum::DEFAULT, PlayerStatusEnum::MUSH)
        ;
        $manager->persist($mushSneezing);

        $manager->flush();

        $this->addReference(self::BITING, $biting);
        $this->addReference(self::BREAKOUTS, $breakouts);
        $this->addReference(self::CAT_ALLERGY_SYMPTOM, $catAllergySymptom);
        $this->addReference(self::CAT_SNEEZING, $catSneezing);
        $this->addReference(self::CONSUME_DRUG_VOMITING, $consumeDrugVomiting);
        $this->addReference(self::CONSUME_VOMITING, $consumeVomiting);
        $this->addReference(self::CYCLE_DIRTINESS, $cycleDirtiness);
        $this->addReference(self::CYCLE_DIRTINESS_RAND_40, $cycleDirtinessRand40);
        $this->addReference(self::DROOLING, $drooling);
        $this->addReference(self::FOAMING_MOUTH, $foamingMouth);
        $this->addReference(self::MOVE_VOMITING, $moveVomiting);
        $this->addReference(self::MUSH_SNEEZING, $mushSneezing);
        $this->addReference(self::ITEM_IN_ROOM_CAT, $catIsInRoomActivationRequirement);
        $this->addReference(self::REASON_MOVE, $moveActionActivationRequirement);
        $this->addReference(self::RANDOM_16, $randActivationRequirement16);
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
