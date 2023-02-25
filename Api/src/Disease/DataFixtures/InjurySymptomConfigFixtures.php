<?php

namespace Mush\Disease\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionTypeEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Disease\Entity\Config\SymptomActivationRequirement;
use Mush\Disease\Entity\Config\SymptomConfig;
use Mush\Disease\Enum\SymptomActivationRequirementEnum;
use Mush\Disease\Enum\SymptomEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;

class InjurySymptomConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public const CANT_MOVE = 'cant_move';
    public const CANT_PICK_UP_HEAVY_ITEMS = 'cant_pick_up_heavy_items';
    public const DEAF = 'deaf';
    public const MUTE = 'mute';
    public const SEPTICEMIA_ON_CYCLE_CHANGE = 'septicemia_on_cycle_change';
    public const SEPTICEMIA_ON_DIRTY_EVENT = 'septicemia_on_dirty_event';
    public const SEPTICEMIA_ON_POST_ACTION = 'septicemia_on_post_action';

    public function load(ObjectManager $manager): void
    {
        $actionDirtyRateActivationRequirement = new SymptomActivationRequirement(SymptomActivationRequirementEnum::ACTION_DIRTY_RATE);
        $actionDirtyRateActivationRequirement->buildName();
        $manager->persist($actionDirtyRateActivationRequirement);

        $dirtyStatusActivationRequirement = new SymptomActivationRequirement(SymptomActivationRequirementEnum::PLAYER_STATUS);
        $dirtyStatusActivationRequirement
            ->setActivationRequirement(PlayerStatusEnum::DIRTY)
            ->buildName()
        ;
        $manager->persist($dirtyStatusActivationRequirement);

        $heavyItemActivationRequirement = new SymptomActivationRequirement(SymptomActivationRequirementEnum::ITEM_STATUS);
        $heavyItemActivationRequirement
            ->setActivationRequirement(EquipmentStatusEnum::HEAVY)
            ->buildName()
        ;
        $manager->persist($heavyItemActivationRequirement);

        $cantMove = new SymptomConfig(SymptomEnum::CANT_MOVE);
        $cantMove
            ->setTrigger(ActionEnum::MOVE)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($cantMove);

        $cantPickUpHeavyItems = new SymptomConfig(SymptomEnum::CANT_PICK_UP_HEAVY_ITEMS);
        $cantPickUpHeavyItems
            ->setTrigger(ActionEnum::TAKE)
            ->addSymptomActivationRequirement($heavyItemActivationRequirement)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($cantPickUpHeavyItems);

        $deaf = new SymptomConfig(SymptomEnum::DEAF);
        $deaf
            ->setTrigger(EventEnum::NEW_MESSAGE)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($deaf);

        $septicemiaOnCycleChange = new SymptomConfig(SymptomEnum::SEPTICEMIA);
        $septicemiaOnCycleChange
            ->setTrigger(EventEnum::NEW_CYCLE)
            ->addSymptomActivationRequirement($dirtyStatusActivationRequirement)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($septicemiaOnCycleChange);

        $septicemiaOnDirtyEvent = new SymptomConfig(SymptomEnum::SEPTICEMIA);
        $septicemiaOnDirtyEvent
            ->setTrigger(StatusEvent::STATUS_APPLIED)
            ->addSymptomActivationRequirement($dirtyStatusActivationRequirement)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($septicemiaOnDirtyEvent);

        $septicemiaOnPostAction = new SymptomConfig(SymptomEnum::SEPTICEMIA);
        $septicemiaOnPostAction
            ->setTrigger(ActionEvent::POST_ACTION)
            ->addSymptomActivationRequirement($actionDirtyRateActivationRequirement)
            ->addSymptomActivationRequirement($dirtyStatusActivationRequirement)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($septicemiaOnPostAction);

        $mute = new SymptomConfig(SymptomEnum::MUTE);
        $mute
            ->setTrigger(ActionTypeEnum::ACTION_SPOKEN)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($mute);

        $manager->flush();

        $this->addReference(self::CANT_MOVE, $cantMove);
        $this->addReference(self::CANT_PICK_UP_HEAVY_ITEMS, $cantPickUpHeavyItems);
        $this->addReference(self::DEAF, $deaf);
        $this->addReference(self::MUTE, $mute);
        $this->addReference(self::SEPTICEMIA_ON_CYCLE_CHANGE, $septicemiaOnCycleChange);
        $this->addReference(self::SEPTICEMIA_ON_DIRTY_EVENT, $septicemiaOnDirtyEvent);
        $this->addReference(self::SEPTICEMIA_ON_POST_ACTION, $septicemiaOnPostAction);
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
