<?php

namespace Mush\Disease\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Disease\Entity\Config\SymptomConfig;
use Mush\Disease\Entity\SymptomCondition;
use Mush\Disease\Enum\SymptomConditionEnum;
use Mush\Disease\Enum\SymptomEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;

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

    public function load(ObjectManager $manager): void
    {
        $catIsInRoomCondition = new SymptomCondition(SymptomConditionEnum::ITEM_IN_ROOM);
        $catIsInRoomCondition->setCondition(ItemEnum::SCHRODINGER);
        $manager->persist($catIsInRoomCondition);

        $consumeActionCondition = new SymptomCondition(SymptomConditionEnum::REASON);
        $consumeActionCondition->setCondition(ActionEnum::CONSUME);
        $manager->persist($consumeActionCondition);

        $consumeDrugActionCondition = new SymptomCondition(SymptomConditionEnum::REASON);
        $consumeDrugActionCondition->setCondition(ActionEnum::CONSUME_DRUG);
        $manager->persist($consumeDrugActionCondition);

        $holdCatCondition = new SymptomCondition(SymptomConditionEnum::PLAYER_EQUIPMENT);
        $holdCatCondition->setCondition(ItemEnum::SCHRODINGER);
        $manager->persist($holdCatCondition);

        $moveActionCondition = new SymptomCondition(SymptomConditionEnum::REASON);
        $moveActionCondition->setCondition(ActionEnum::MOVE);
        $manager->persist($moveActionCondition);

        $mushIsInRoomCondition = new SymptomCondition(SymptomConditionEnum::PLAYER_IN_ROOM);
        $mushIsInRoomCondition->setCondition(SymptomConditionEnum::MUSH_IN_ROOM);
        $manager->persist($mushIsInRoomCondition);

        $notAloneCondition = new SymptomCondition(SymptomConditionEnum::PLAYER_IN_ROOM);
        $notAloneCondition->setCondition(SymptomConditionEnum::NOT_ALONE);
        $manager->persist($notAloneCondition);

        $randCondition16 = new SymptomCondition(SymptomConditionEnum::RANDOM);
        $randCondition16->setValue(16);
        $manager->persist($randCondition16);

        $randCondition40 = new SymptomCondition(SymptomConditionEnum::RANDOM);
        $randCondition40->setValue(40);
        $manager->persist($randCondition40);

        $biting = new SymptomConfig(SymptomEnum::BITING);
        $biting
            ->setTrigger(EventEnum::NEW_CYCLE)
            ->addSymptomCondition($notAloneCondition)
            ->addSymptomCondition($randCondition16)
        ;
        $manager->persist($biting);

        $breakouts = new SymptomConfig(SymptomEnum::BREAKOUTS);
        $breakouts
            ->setTrigger(ActionEvent::POST_ACTION)
            ->addSymptomCondition($randCondition16)
            ->addSymptomCondition($moveActionCondition)
        ;
        $manager->persist($breakouts);

        $catAllergySymptom = new SymptomConfig(SymptomEnum::CAT_ALLERGY);
        $catAllergySymptom
            ->setTrigger(ActionEvent::POST_ACTION)
            ->addSymptomCondition($holdCatCondition)
        ;
        $manager->persist($catAllergySymptom);

        $catSneezing = new SymptomConfig(SymptomEnum::SNEEZING);
        $catSneezing
            ->setTrigger(ActionEvent::POST_ACTION)
            ->addSymptomCondition($moveActionCondition)
            ->addSymptomCondition($catIsInRoomCondition)
        ;
        $manager->persist($catSneezing);

        $consumeDrugVomiting = new SymptomConfig(SymptomEnum::VOMITING);
        $consumeDrugVomiting
            ->setTrigger(ActionEvent::POST_ACTION)
            ->setVisibility(VisibilityEnum::SECRET)
            ->addSymptomCondition($consumeDrugActionCondition)
        ;
        $manager->persist($consumeDrugVomiting);

        $consumeVomiting = new SymptomConfig(SymptomEnum::VOMITING);
        $consumeVomiting
            ->setTrigger(ActionEvent::POST_ACTION)
            ->setVisibility(VisibilityEnum::SECRET)
            ->addSymptomCondition($consumeActionCondition)
        ;
        $manager->persist($consumeVomiting);

        $cycleDirtiness = new SymptomConfig(SymptomEnum::DIRTINESS);
        $cycleDirtiness
            ->setTrigger(EventEnum::NEW_CYCLE)
            ->setVisibility(VisibilityEnum::HIDDEN)
        ;
        $manager->persist($cycleDirtiness);

        $cycleDirtinessRand40 = new SymptomConfig(SymptomEnum::DIRTINESS);
        $cycleDirtinessRand40
            ->setTrigger(EventEnum::NEW_CYCLE)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->addSymptomCondition($randCondition40)
        ;
        $manager->persist($cycleDirtinessRand40);

        $drooling = new SymptomConfig(SymptomEnum::DROOLING);
        $drooling
            ->setTrigger(ActionEvent::POST_ACTION)
            ->addSymptomCondition($randCondition16)
            ->addSymptomCondition($moveActionCondition)

        ;
        $manager->persist($drooling);

        $foamingMouth = new SymptomConfig(SymptomEnum::FOAMING_MOUTH);
        $foamingMouth
            ->setTrigger(ActionEvent::POST_ACTION)
            ->addSymptomCondition($randCondition16)
            ->addSymptomCondition($moveActionCondition)
        ;
        $manager->persist($foamingMouth);

        $moveVomiting = new SymptomConfig(SymptomEnum::VOMITING);
        $moveVomiting
            ->setTrigger(ActionEvent::POST_ACTION)
            ->addSymptomCondition($moveActionCondition)
            ->addSymptomCondition($randCondition16)
        ;
        $manager->persist($moveVomiting);

        $mushSneezing = new SymptomConfig(SymptomEnum::SNEEZING);
        $mushSneezing
            ->setTrigger(ActionEvent::POST_ACTION)
            ->addSymptomCondition($mushIsInRoomCondition)
            ->addSymptomCondition($moveActionCondition)
            ->addSymptomCondition($randCondition16)
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
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
