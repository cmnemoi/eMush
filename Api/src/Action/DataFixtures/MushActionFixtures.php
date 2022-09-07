<?php

namespace Mush\Action\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Action\Enum\ActionTypeEnum;

class MushActionFixtures extends Fixture implements DependentFixtureInterface
{
    public const SPREAD_FIRE = 'spread.fire';
    public const EXTRACT_SPORE = 'extract.spore';
    public const INFECT_PLAYER = 'infect.player';
    public const MAKE_SICK = 'make.sick';
    public const FAKE_DISEASE = 'fake.disease';
    public const SCREW_TALKIE = 'screw.talkie';

    public function load(ObjectManager $manager): void
    {
        /** @var ActionCost $oneActionPointCost */
        $oneActionPointCost = $this->getReference(ActionCostFixture::ACTION_COST_ONE_ACTION);
        /** @var ActionCost $twoActionPointCost */
        $twoActionPointCost = $this->getReference(ActionCostFixture::ACTION_COST_TWO_ACTION);
        /** @var ActionCost $threeActionPointCost */
        $threeActionPointCost = $this->getReference(ActionCostFixture::ACTION_COST_THREE_ACTION);
        /** @var ActionCost $fourActionPointCost */
        $fourActionPointCost = $this->getReference(ActionCostFixture::ACTION_COST_FOUR_ACTION);

        $extractSporeAction = new Action();
        $extractSporeAction
            ->setName(ActionEnum::EXTRACT_SPORE)
            ->setScope(ActionScopeEnum::SELF)
            ->setActionCost($twoActionPointCost)
            ->setDirtyRate(101)
        ;

        $manager->persist($extractSporeAction);

        $infectAction = new Action();
        $infectAction
            ->setName(ActionEnum::INFECT)
            ->setTypes([ActionTypeEnum::ACTION_AGGRESSIVE])
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setActionCost($oneActionPointCost)
        ;

        $manager->persist($infectAction);

        $spreadFireAction = new Action();
        $spreadFireAction
            ->setName(ActionEnum::SPREAD_FIRE)
            ->setScope(ActionScopeEnum::SELF)
            ->setActionCost($fourActionPointCost)
        ;

        $manager->persist($spreadFireAction);

        $makeSickAction = new Action();
        $makeSickAction
            ->setName(ActionEnum::MAKE_SICK)
            ->setTypes([ActionTypeEnum::ACTION_AGGRESSIVE])
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setActionCost($oneActionPointCost)
        ;

        $manager->persist($makeSickAction);

        $fakeDiseaseAction = new Action();
        $fakeDiseaseAction
            ->setName(ActionEnum::FAKE_DISEASE)
            ->setScope(ActionScopeEnum::SELF)
            ->setActionCost($oneActionPointCost)
            ->setDirtyRate(60)
        ;

        $manager->persist($fakeDiseaseAction);

        $screwTalkieAction = new Action();
        $screwTalkieAction
            ->setName(ActionEnum::SCREW_TALKIE)
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setTypes([ActionTypeEnum::ACTION_AGGRESSIVE])
            ->setActionCost($threeActionPointCost)
        ;

        $manager->persist($screwTalkieAction);

        $manager->flush();

        $this->addReference(self::EXTRACT_SPORE, $extractSporeAction);
        $this->addReference(self::INFECT_PLAYER, $infectAction);
        $this->addReference(self::SPREAD_FIRE, $spreadFireAction);
        $this->addReference(self::MAKE_SICK, $makeSickAction);
        $this->addReference(self::FAKE_DISEASE, $fakeDiseaseAction);
        $this->addReference(self::SCREW_TALKIE, $screwTalkieAction);
    }

    public function getDependencies(): array
    {
        return [
            ActionCostFixture::class,
        ];
    }
}
