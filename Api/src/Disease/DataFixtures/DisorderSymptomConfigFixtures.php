<?php

namespace Mush\Disease\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\Enum\ActionTypeEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Disease\Entity\Config\SymptomCondition;
use Mush\Disease\Entity\Config\SymptomConfig;
use Mush\Disease\Enum\SymptomConditionEnum;
use Mush\Disease\Enum\SymptomEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\GameConfigEnum;

class DisorderSymptomConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public const FEAR_OF_CATS = 'fear_of_cats';
    public const NO_ATTACK_ACTIONS = 'no_attack_actions';
    public const NO_PILOTING_ACTIONS = 'no_piloting_actions';
    public const NO_SHOOT_ACTIONS = 'no_shoot_actions';
    public const PSYCHOTIC_ATTACKS = 'psychotic_attacks';
    public const COPROLALIA_MESSAGES = 'coprolalia_messages';
    public const PARANOIA_MESSAGES = 'paranoia_messages';

    public function load(ObjectManager $manager): void
    {
        /** @var SymptomCondition $catIsInRoomCondition */
        $catIsInRoomCondition = $this->getReference(DiseaseSymptomConfigFixtures::ITEM_IN_ROOM_CAT);
        /** @var SymptomCondition $moveActionCondition */
        $moveActionCondition = $this->getReference(DiseaseSymptomConfigFixtures::REASON_MOVE);
        /** @var SymptomCondition $randCondition16 */
        $randCondition16 = $this->getReference(DiseaseSymptomConfigFixtures::RANDOM_16);

        $randCondition50 = new SymptomCondition(SymptomConditionEnum::RANDOM);
        $randCondition50
            ->setValue(50)
            ->buildName()
        ;
        $manager->persist($randCondition50);

        $fearOfCats = new SymptomConfig(SymptomEnum::FEAR_OF_CATS);
        $fearOfCats
            ->setTrigger(ActionEvent::POST_ACTION)
            ->addSymptomCondition($catIsInRoomCondition)
            ->addSymptomCondition($moveActionCondition)
            ->addSymptomCondition($randCondition50)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($fearOfCats);

        $noAttackActions = new SymptomConfig(SymptomEnum::NO_ATTACK_ACTIONS);
        $noAttackActions
            ->setTrigger(ActionTypeEnum::ACTION_ATTACK)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($noAttackActions);

        $noPilotingActions = new SymptomConfig(SymptomEnum::NO_PILOTING_ACTIONS);
        $noPilotingActions
            ->setTrigger(ActionTypeEnum::ACTION_PILOT)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($noPilotingActions);

        $noShootActions = new SymptomConfig(SymptomEnum::NO_SHOOT_ACTIONS);
        $noShootActions
            ->setTrigger(ActionTypeEnum::ACTION_SHOOT)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($noShootActions);

        $psychoticAttacks = new SymptomConfig(SymptomEnum::PSYCHOTIC_ATTACKS);
        $psychoticAttacks
            ->setTrigger(ActionEvent::POST_ACTION)
            ->addSymptomCondition($moveActionCondition)
            ->addSymptomCondition($randCondition16)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($psychoticAttacks);

        $coprolalia = new SymptomConfig(SymptomEnum::COPROLALIA_MESSAGES);
        $coprolalia
            ->setTrigger(EventEnum::NEW_MESSAGE)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($coprolalia);

        $paranoia = new SymptomConfig(SymptomEnum::PARANOIA_MESSAGES);
        $paranoia
            ->setTrigger(EventEnum::NEW_MESSAGE)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($paranoia);

        $manager->flush();

        $this->addReference(self::FEAR_OF_CATS, $fearOfCats);
        $this->addReference(self::NO_ATTACK_ACTIONS, $noAttackActions);
        $this->addReference(self::NO_PILOTING_ACTIONS, $noPilotingActions);
        $this->addReference(self::NO_SHOOT_ACTIONS, $noShootActions);
        $this->addReference(self::PSYCHOTIC_ATTACKS, $psychoticAttacks);
        $this->addReference(self::COPROLALIA_MESSAGES, $coprolalia);
        $this->addReference(self::PARANOIA_MESSAGES, $paranoia);
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
            DiseaseSymptomConfigFixtures::class,
        ];
    }
}
