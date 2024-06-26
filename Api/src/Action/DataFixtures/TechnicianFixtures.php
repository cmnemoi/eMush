<?php

namespace Mush\Action\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionHolderEnum;
use Mush\Action\Enum\ActionRangeEnum;
use Mush\Action\Enum\ActionTypeEnum;
use Mush\Game\Enum\ActionOutputEnum;
use Mush\Game\Enum\VisibilityEnum;

class TechnicianFixtures extends Fixture
{
    public const DISMANTLE_3_12 = 'dismantle.3.12';
    public const DISMANTLE_3_25 = 'dismantle.3.25';
    public const DISMANTLE_3_50 = 'dismantle.3.50';
    public const DISMANTLE_4_6 = 'dismantle.4.6';
    public const DISMANTLE_4_12 = 'dismantle.4.12';
    public const DISMANTLE_4_25 = 'dismantle.4.25';

    public const REPAIR_1 = 'repair.1';
    public const REPAIR_3 = 'repair.3';
    public const REPAIR_6 = 'repair.6';
    public const REPAIR_12 = 'repair.12';
    public const REPAIR_25 = 'repair.25';
    public const REPAIR_50 = 'repair.50';

    public const SABOTAGE_1 = 'sabotage.1';
    public const SABOTAGE_3 = 'sabotage.3';
    public const SABOTAGE_6 = 'sabotage.6';
    public const SABOTAGE_12 = 'sabotage.12';
    public const SABOTAGE_25 = 'sabotage.25';
    public const SABOTAGE_50 = 'sabotage.50';

    public function load(ObjectManager $manager): void
    {
        $repair1 = new ActionConfig();
        $repair1
            ->setName(ActionEnum::REPAIR->value . '_percent_1')
            ->setActionName(ActionEnum::REPAIR)
            ->setTypes([ActionTypeEnum::ACTION_TECHNICIAN])
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(1)
            ->setDirtyRate(20)
            ->setInjuryRate(4)
            ->setSuccessRate(1);
        $manager->persist($repair1);

        $repair3 = new ActionConfig();
        $repair3
            ->setName(ActionEnum::REPAIR->value . '_percent_3')
            ->setActionName(ActionEnum::REPAIR)
            ->setTypes([ActionTypeEnum::ACTION_TECHNICIAN])
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(1)
            ->setDirtyRate(20)
            ->setInjuryRate(4)
            ->setSuccessRate(3);
        $manager->persist($repair3);

        $repair6 = new ActionConfig();
        $repair6
            ->setName(ActionEnum::REPAIR->value . '_percent_6')
            ->setActionName(ActionEnum::REPAIR)
            ->setTypes([ActionTypeEnum::ACTION_TECHNICIAN])
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(1)
            ->setDirtyRate(20)
            ->setInjuryRate(4)
            ->setSuccessRate(6);
        $manager->persist($repair6);

        $repair12 = new ActionConfig();
        $repair12
            ->setName(ActionEnum::REPAIR->value . '_percent_12')
            ->setActionName(ActionEnum::REPAIR)
            ->setTypes([ActionTypeEnum::ACTION_TECHNICIAN])
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(1)
            ->setDirtyRate(20)
            ->setInjuryRate(4)
            ->setSuccessRate(12);
        $manager->persist($repair12);

        $repair25 = new ActionConfig();
        $repair25
            ->setName(ActionEnum::REPAIR->value . '_percent_25')
            ->setActionName(ActionEnum::REPAIR)
            ->setTypes([ActionTypeEnum::ACTION_TECHNICIAN])
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(1)
            ->setDirtyRate(20)
            ->setInjuryRate(4)
            ->setSuccessRate(25);
        $manager->persist($repair25);

        $repair50 = new ActionConfig();
        $repair50
            ->setName(ActionEnum::REPAIR->value . '_percent_50')
            ->setActionName(ActionEnum::REPAIR)
            ->setTypes([ActionTypeEnum::ACTION_TECHNICIAN])
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(1)
            ->setDirtyRate(20)
            ->setInjuryRate(4)
            ->setSuccessRate(50);
        $manager->persist($repair50);

        $dismantle312 = new ActionConfig();
        $dismantle312
            ->setName(ActionEnum::DISASSEMBLE->value . '_percent_12_cost_3')
            ->setActionName(ActionEnum::DISASSEMBLE)
            ->setTypes([ActionTypeEnum::ACTION_TECHNICIAN])
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(3)
            ->setDirtyRate(25)
            ->setInjuryRate(5)
            ->setSuccessRate(12);
        $manager->persist($dismantle312);

        $dismantle325 = new ActionConfig();
        $dismantle325
            ->setName(ActionEnum::DISASSEMBLE->value . '_percent_25_cost_3')
            ->setActionName(ActionEnum::DISASSEMBLE)
            ->setTypes([ActionTypeEnum::ACTION_TECHNICIAN])
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(3)
            ->setDirtyRate(25)
            ->setInjuryRate(5)
            ->setSuccessRate(25);
        $manager->persist($dismantle325);

        $dismantle350 = new ActionConfig();
        $dismantle350
            ->setName(ActionEnum::DISASSEMBLE->value . '_percent_50_cost_3')
            ->setActionName(ActionEnum::DISASSEMBLE)
            ->setTypes([ActionTypeEnum::ACTION_TECHNICIAN])
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(3)
            ->setDirtyRate(25)
            ->setInjuryRate(5)
            ->setSuccessRate(50);
        $manager->persist($dismantle350);

        $dismantle46 = new ActionConfig();
        $dismantle46
            ->setName(ActionEnum::DISASSEMBLE->value . '_percent_6_cost_4')
            ->setActionName(ActionEnum::DISASSEMBLE)
            ->setTypes([ActionTypeEnum::ACTION_TECHNICIAN])
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(4)
            ->setDirtyRate(25)
            ->setInjuryRate(5)
            ->setSuccessRate(6);
        $manager->persist($dismantle46);

        $dismantle412 = new ActionConfig();
        $dismantle412
            ->setName(ActionEnum::DISASSEMBLE->value . '_percent_12_cost_4')
            ->setActionName(ActionEnum::DISASSEMBLE)
            ->setTypes([ActionTypeEnum::ACTION_TECHNICIAN])
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(4)
            ->setDirtyRate(25)
            ->setInjuryRate(5)
            ->setSuccessRate(12);
        $manager->persist($dismantle412);

        $dismantle425 = new ActionConfig();
        $dismantle425
            ->setName(ActionEnum::DISASSEMBLE->value . '_percent_25_cost_4')
            ->setActionName(ActionEnum::DISASSEMBLE)
            ->setTypes([ActionTypeEnum::ACTION_TECHNICIAN])
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(4)
            ->setDirtyRate(25)
            ->setInjuryRate(5)
            ->setSuccessRate(25);
        $manager->persist($dismantle425);

        $sabotageAction1 = new ActionConfig();
        $sabotageAction1
            ->setName(ActionEnum::SABOTAGE->value . '_percent_1')
            ->setActionName(ActionEnum::SABOTAGE)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(2)
            ->setDirtyRate(25)
            ->setSuccessRate(1)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::SECRET);

        $manager->persist($sabotageAction1);

        $sabotageAction3 = new ActionConfig();
        $sabotageAction3
            ->setName(ActionEnum::SABOTAGE->value . '_percent_3')
            ->setActionName(ActionEnum::SABOTAGE)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(2)
            ->setDirtyRate(25)
            ->setSuccessRate(3)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::SECRET);

        $manager->persist($sabotageAction3);

        $sabotageAction6 = new ActionConfig();
        $sabotageAction6
            ->setName(ActionEnum::SABOTAGE->value . '_percent_6')
            ->setActionName(ActionEnum::SABOTAGE)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(2)
            ->setDirtyRate(25)
            ->setSuccessRate(6)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::SECRET);

        $manager->persist($sabotageAction6);

        $sabotageAction12 = new ActionConfig();
        $sabotageAction12
            ->setName(ActionEnum::SABOTAGE->value . '_percent_12')
            ->setActionName(ActionEnum::SABOTAGE)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(2)
            ->setDirtyRate(25)
            ->setSuccessRate(12)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::SECRET);

        $manager->persist($sabotageAction12);

        $sabotageAction25 = new ActionConfig();
        $sabotageAction25
            ->setName(ActionEnum::SABOTAGE->value . '_percent_25')
            ->setActionName(ActionEnum::SABOTAGE)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(2)
            ->setDirtyRate(25)
            ->setSuccessRate(25)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::SECRET);

        $manager->persist($sabotageAction25);

        $sabotageAction50 = new ActionConfig();
        $sabotageAction50
            ->setName(ActionEnum::SABOTAGE->value . '_percent_50')
            ->setActionName(ActionEnum::SABOTAGE)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(2)
            ->setDirtyRate(25)
            ->setSuccessRate(50)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE);

        $manager->persist($sabotageAction50);

        $manager->flush();

        $this->addReference(self::DISMANTLE_3_12, $dismantle312);
        $this->addReference(self::DISMANTLE_3_25, $dismantle325);
        $this->addReference(self::DISMANTLE_3_50, $dismantle350);
        $this->addReference(self::DISMANTLE_4_6, $dismantle46);
        $this->addReference(self::DISMANTLE_4_12, $dismantle412);
        $this->addReference(self::DISMANTLE_4_25, $dismantle425);
        $this->addReference(self::REPAIR_1, $repair1);
        $this->addReference(self::REPAIR_3, $repair3);
        $this->addReference(self::REPAIR_6, $repair6);
        $this->addReference(self::REPAIR_12, $repair12);
        $this->addReference(self::REPAIR_25, $repair25);
        $this->addReference(self::REPAIR_50, $repair50);
        $this->addReference(self::SABOTAGE_1, $sabotageAction1);
        $this->addReference(self::SABOTAGE_3, $sabotageAction3);
        $this->addReference(self::SABOTAGE_6, $sabotageAction6);
        $this->addReference(self::SABOTAGE_12, $sabotageAction12);
        $this->addReference(self::SABOTAGE_25, $sabotageAction25);
        $this->addReference(self::SABOTAGE_50, $sabotageAction50);
    }
}
