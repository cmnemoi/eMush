<?php

namespace Mush\Action\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;

class TechnicianFixtures extends Fixture implements DependentFixtureInterface
{
    public const DISMANTLE_3_12 = 'dismantle.3.12';
    public const DISMANTLE_3_25 = 'dismantle.3.25';
    public const DISMANTLE_3_50 = 'dismantle.3.50';
    public const DISMANTLE_4_12 = 'dismantle.4.12';
    public const DISMANTLE_4_25 = 'dismantle.4.25';

    public function load(ObjectManager $manager): void
    {
        /** @var ActionCost $threeActionPointCost */
        $threeActionPointCost = $this->getReference(ActionCostFixture::ACTION_COST_THREE_ACTION);
        /** @var ActionCost $fourActionPointCost */
        $fourActionPointCost = $this->getReference(ActionCostFixture::ACTION_COST_FOUR_ACTION);

        $dismantle312 = new Action();
        $dismantle312
            ->setName(ActionEnum::DISASSEMBLE)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(1)
            ->setSuccessRate(12)
            ->setActionCost($threeActionPointCost)
        ;
        $manager->persist($dismantle312);

        $dismantle325 = new Action();
        $dismantle325
            ->setName(ActionEnum::DISASSEMBLE)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(1)
            ->setSuccessRate(25)
            ->setActionCost($threeActionPointCost)
        ;
        $manager->persist($dismantle325);

        $dismantle350 = new Action();
        $dismantle350
            ->setName(ActionEnum::DISASSEMBLE)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(1)
            ->setSuccessRate(50)
            ->setActionCost($threeActionPointCost)
        ;
        $manager->persist($dismantle350);

        $dismantle412 = new Action();
        $dismantle412
            ->setName(ActionEnum::DISASSEMBLE)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(1)
            ->setSuccessRate(12)
            ->setActionCost($fourActionPointCost)
        ;
        $manager->persist($dismantle412);

        $dismantle425 = new Action();
        $dismantle425
            ->setName(ActionEnum::DISASSEMBLE)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(1)
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
    }

    public function getDependencies(): array
    {
        return [
            ActionCostFixture::class,
        ];
    }
}
