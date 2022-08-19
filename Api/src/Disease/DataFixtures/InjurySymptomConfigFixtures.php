<?php

namespace Mush\Disease\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\Enum\ActionEnum;
use Mush\Disease\Entity\Config\SymptomConfig;
use Mush\Disease\Entity\SymptomCondition;
use Mush\Disease\Enum\SymptomConditionEnum;
use Mush\Disease\Enum\SymptomEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Enum\EventEnum;
use Mush\Status\Enum\EquipmentStatusEnum;

class InjurySymptomConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public const CANT_MOVE = 'cant_move';
    public const CANT_PICK_UP_HEAVY_ITEMS = 'cant_pick_up_heavy_items';
    public const DEAF = 'deaf';
    public const MUTE = 'mute';

    public function load(ObjectManager $manager): void
    {
        $heavyItemCondition = new SymptomCondition(SymptomConditionEnum::ITEM_STATUS);
        $heavyItemCondition->setCondition(EquipmentStatusEnum::HEAVY);
        $manager->persist($heavyItemCondition);

        $cantMove = new SymptomConfig(SymptomEnum::CANT_MOVE);
        $cantMove->setTrigger(ActionEnum::MOVE);
        $manager->persist($cantMove);

        $cantPickUpHeavyItems = new SymptomConfig(SymptomEnum::CANT_PICK_UP_HEAVY_ITEMS);
        $cantPickUpHeavyItems->setTrigger(ActionEnum::TAKE);
        $cantPickUpHeavyItems->addSymptomCondition($heavyItemCondition);
        $manager->persist($cantPickUpHeavyItems);

        $deaf = new SymptomConfig(SymptomEnum::DEAF);
        $manager->persist($deaf);

        $mute = new SymptomConfig(SymptomEnum::MUTE);
        $mute->setTrigger(EventEnum::ON_NEW_MESSAGE);
        $manager->persist($mute);

        $manager->flush();

        $this->addReference(self::CANT_MOVE, $cantMove);
        $this->addReference(self::CANT_PICK_UP_HEAVY_ITEMS, $cantPickUpHeavyItems);
        $this->addReference(self::DEAF, $deaf);
        $this->addReference(self::MUTE, $mute);
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
