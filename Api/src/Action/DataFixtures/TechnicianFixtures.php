<?php

namespace Mush\Action\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Player\Enum\ModifierScopeEnum;

class TechnicianFixtures extends Fixture implements DependentFixtureInterface
{
    public const DISMANTLE_3_12 = 'dismantle.3.12';
    public const DISMANTLE_3_25 = 'dismantle.3.25';
    public const DISMANTLE_3_50 = 'dismantle.3.50';
    public const DISMANTLE_4_12 = 'dismantle.4.12';
    public const DISMANTLE_4_25 = 'dismantle.4.25';
    public const REPAIR_3 = 'repair.3';
    public const REPAIR_6 = 'repair.6';
    public const REPAIR_12 = 'repair.12';
    public const REPAIR_25 = 'repair.25';
    public const REPAIR_50 = 'repair.50';

    public function load(ObjectManager $manager): void
    {
        /** @var ActionCost $oneActionPointCost */
        $oneActionPointCost = $this->getReference(ActionCostFixture::ACTION_COST_ONE_ACTION);
        /** @var ActionCost $threeActionPointCost */
        $threeActionPointCost = $this->getReference(ActionCostFixture::ACTION_COST_THREE_ACTION);
        /** @var ActionCost $fourActionPointCost */
        $fourActionPointCost = $this->getReference(ActionCostFixture::ACTION_COST_FOUR_ACTION);

        $repair3 = new Action();
        $repair3
            ->setName(ActionEnum::REPAIR)
            ->setTypes([ModifierScopeEnum::ACTION_TECHNICIAN])
            ->setScope(ActionScopeEnum::CURRENT)
            ->setDirtyRate(25)
            ->setInjuryRate(25)
            ->setSuccessRate(3)
            ->setActionCost($oneActionPointCost)
        ;
        $manager->persist($repair3);

        $repair6 = new Action();
        $repair6
            ->setName(ActionEnum::REPAIR)
            ->setTypes([ModifierScopeEnum::ACTION_TECHNICIAN])
            ->setScope(ActionScopeEnum::CURRENT)
            ->setDirtyRate(25)
            ->setInjuryRate(25)
            ->setSuccessRate(6)
            ->setActionCost($oneActionPointCost)
        ;
        $manager->persist($repair6);

        $repair12 = new Action();
        $repair12
            ->setName(ActionEnum::REPAIR)
            ->setTypes([ModifierScopeEnum::ACTION_TECHNICIAN])
            ->setScope(ActionScopeEnum::CURRENT)
            ->setDirtyRate(25)
            ->setInjuryRate(25)
            ->setSuccessRate(12)
            ->setActionCost($oneActionPointCost)
        ;
        $manager->persist($repair12);

        $repair25 = new Action();
        $repair25
            ->setName(ActionEnum::REPAIR)
            ->setTypes([ModifierScopeEnum::ACTION_TECHNICIAN])
            ->setScope(ActionScopeEnum::CURRENT)
            ->setDirtyRate(25)
            ->setInjuryRate(25)
            ->setSuccessRate(25)
            ->setActionCost($oneActionPointCost)
        ;
        $manager->persist($repair25);

        $repair50 = new Action();
        $repair50
            ->setName(ActionEnum::REPAIR)
            ->setTypes([ModifierScopeEnum::ACTION_TECHNICIAN])
            ->setScope(ActionScopeEnum::CURRENT)
            ->setDirtyRate(25)
            ->setInjuryRate(25)
            ->setSuccessRate(50)
            ->setActionCost($oneActionPointCost)
        ;
        $manager->persist($repair50);

        $dismantle312 = new Action();
        $dismantle312
            ->setName(ActionEnum::DISASSEMBLE)
            ->setTypes([ModifierScopeEnum::ACTION_TECHNICIAN])
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(1)
            ->setSuccessRate(12)
            ->setActionCost($threeActionPointCost)
        ;
        $manager->persist($dismantle312);

        $dismantle325 = new Action();
        $dismantle325
            ->setName(ActionEnum::DISASSEMBLE)
            ->setTypes([ModifierScopeEnum::ACTION_TECHNICIAN])
            ->setScope(ActionScopeEnum::CURRENT)
            ->setDirtyRate(25)
            ->setInjuryRate(25)
            ->setSuccessRate(25)
            ->setActionCost($threeActionPointCost)
        ;
        $manager->persist($dismantle325);

        $dismantle350 = new Action();
        $dismantle350
            ->setName(ActionEnum::DISASSEMBLE)
            ->setTypes([ModifierScopeEnum::ACTION_TECHNICIAN])
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(1)
            ->setSuccessRate(50)
            ->setActionCost($threeActionPointCost)
        ;
        $manager->persist($dismantle350);

        $dismantle412 = new Action();
        $dismantle412
            ->setName(ActionEnum::DISASSEMBLE)
            ->setTypes([ModifierScopeEnum::ACTION_TECHNICIAN])
            ->setScope(ActionScopeEnum::CURRENT)
            ->setDirtyRate(25)
            ->setInjuryRate(25)
            ->setSuccessRate(12)
            ->setActionCost($fourActionPointCost)
        ;
        $manager->persist($dismantle412);

        $dismantle425 = new Action();
        $dismantle425
            ->setName(ActionEnum::DISASSEMBLE)
            ->setTypes([ModifierScopeEnum::ACTION_TECHNICIAN])
            ->setScope(ActionScopeEnum::CURRENT)
            ->setDirtyRate(25)
            ->setInjuryRate(25)
            ->setSuccessRate(25)
            ->setActionCost($fourActionPointCost)
        ;
        $manager->persist($dismantle425);

        $manager->flush();

        $this->addReference(self::DISMANTLE_3_12, $dismantle312);
        $this->addReference(self::DISMANTLE_3_25, $dismantle325);
        $this->addReference(self::DISMANTLE_3_50, $dismantle350);
        $this->addReference(self::DISMANTLE_4_12, $dismantle412);
        $this->addReference(self::DISMANTLE_4_25, $dismantle425);
        $this->addReference(self::REPAIR_3, $repair3);
        $this->addReference(self::REPAIR_6, $repair6);
        $this->addReference(self::REPAIR_12, $repair12);
        $this->addReference(self::REPAIR_25, $repair25);
        $this->addReference(self::REPAIR_50, $repair50);
    }

    public function getDependencies(): array
    {
        return [
            ActionCostFixture::class,
        ];
    }
}
