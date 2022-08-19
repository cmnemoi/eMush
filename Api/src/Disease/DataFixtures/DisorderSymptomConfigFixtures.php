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

class DisorderSymptomConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public const FEAR_OF_CATS = 'fear_of_cats';
    public const NO_PILOTING_ACTIONS = 'no_piloting_actions';

    public function load(ObjectManager $manager): void
    {
        $catIsInRoomCondition = new SymptomCondition(SymptomConditionEnum::ITEM_IN_ROOM);
        $catIsInRoomCondition->setCondition(ItemEnum::SCHRODINGER);
        $manager->persist($catIsInRoomCondition);

        $moveActionCondition = new SymptomCondition(SymptomConditionEnum::REASON);
        $moveActionCondition->setCondition(ActionEnum::MOVE);
        $manager->persist($moveActionCondition);

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

        $noPilotingActions = new SymptomConfig(SymptomEnum::NO_PILOTING_ACTIONS);
        $manager->persist($noPilotingActions);

        $manager->flush();

        $this->addReference(self::FEAR_OF_CATS, $fearOfCats);
        $this->addReference(self::NO_PILOTING_ACTIONS, $noPilotingActions);
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
