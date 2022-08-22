<?php

namespace Mush\Disease\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionTypeEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Disease\Entity\Config\SymptomConfig;
use Mush\Disease\Entity\SymptomCondition;
use Mush\Disease\Enum\SymptomConditionEnum;
use Mush\Disease\Enum\SymptomEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Enum\EventEnum;

class DisorderSymptomConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public const FEAR_OF_CATS = 'fear_of_cats';
    public const NO_ATTACK_ACTIONS = 'no_attack_actions';
    public const NO_PILOTING_ACTIONS = 'no_piloting_actions';
    public const NO_SHOOT_ACTIONS = 'no_shoot_actions';
    public const PSYCHOTIC_ATTACKS = 'psychotic_attacks';

    public function load(ObjectManager $manager): void
    {
        $catIsInRoomCondition = new SymptomCondition(SymptomConditionEnum::ITEM_IN_ROOM);
        $catIsInRoomCondition->setCondition(ItemEnum::SCHRODINGER);
        $manager->persist($catIsInRoomCondition);

        $moveActionCondition = new SymptomCondition(SymptomConditionEnum::REASON);
        $moveActionCondition->setCondition(ActionEnum::MOVE);
        $manager->persist($moveActionCondition);

        $randCondition16 = new SymptomCondition(SymptomConditionEnum::RANDOM);
        $randCondition16->setValue(16);
        $manager->persist($randCondition16);

        $randCondition50 = new SymptomCondition(SymptomConditionEnum::RANDOM);
        $randCondition50->setValue(50);
        $manager->persist($randCondition50);

        $fearOfCats = new SymptomConfig(SymptomEnum::FEAR_OF_CATS);
        $fearOfCats
            ->setTrigger(ActionEvent::POST_ACTION)
            ->addSymptomCondition($catIsInRoomCondition)
            ->addSymptomCondition($moveActionCondition)
            ->addSymptomCondition($randCondition50)
        ;
        $manager->persist($fearOfCats);

        $noAttackActions = new SymptomConfig(SymptomEnum::NO_ATTACK_ACTIONS);
        $noAttackActions
            ->setTrigger(ActionTypeEnum::ACTION_ATTACK)
        ;
        $manager->persist($noAttackActions);

        $noPilotingActions = new SymptomConfig(SymptomEnum::NO_PILOTING_ACTIONS);
        $noPilotingActions
            ->setTrigger(ActionTypeEnum::ACTION_PILOT)
        ;
        $manager->persist($noPilotingActions);

        $noShootActions = new SymptomConfig(SymptomEnum::NO_SHOOT_ACTIONS);
        $noShootActions
            ->setTrigger(ActionTypeEnum::ACTION_SHOOT)
        ;
        $manager->persist($noShootActions);

        $psychoticAttacks = new SymptomConfig(SymptomEnum::PSYCHOTIC_ATTACKS);
        $psychoticAttacks
            ->setTrigger(EventEnum::NEW_CYCLE)
            ->addSymptomCondition($randCondition16)
        ;
        $manager->persist($psychoticAttacks);

        $manager->flush();

        $this->addReference(self::FEAR_OF_CATS, $fearOfCats);
        $this->addReference(self::NO_ATTACK_ACTIONS, $noAttackActions);
        $this->addReference(self::NO_PILOTING_ACTIONS, $noPilotingActions);
        $this->addReference(self::NO_SHOOT_ACTIONS, $noShootActions);
        $this->addReference(self::PSYCHOTIC_ATTACKS, $psychoticAttacks);
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
