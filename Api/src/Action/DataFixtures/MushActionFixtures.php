<?php

namespace Mush\Action\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Action\Enum\ActionTypeEnum;
use Mush\Game\Enum\ActionOutputEnum;
use Mush\Game\Enum\VisibilityEnum;

class MushActionFixtures extends Fixture
{
    public const SPREAD_FIRE = 'spread.fire';
    public const EXTRACT_SPORE = 'extract.spore';
    public const INFECT_PLAYER = 'infect.player';
    public const MAKE_SICK = 'make.sick';
    public const FAKE_DISEASE = 'fake.disease';
    public const SCREW_TALKIE = 'screw.talkie';
    public const PHAGOCYTE = 'phagocyte';

    public function load(ObjectManager $manager): void
    {
        $extractSporeAction = new Action();
        $extractSporeAction
            ->setName(ActionEnum::EXTRACT_SPORE)
            ->setActionName(ActionEnum::EXTRACT_SPORE)
            ->setScope(ActionScopeEnum::SELF)
            ->setActionCost(2)
            ->setDirtyRate(100)
            ->makeSuperDirty()
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::SECRET);

        $manager->persist($extractSporeAction);

        $infectAction = new Action();
        $infectAction
            ->setName(ActionEnum::INFECT)
            ->setActionName(ActionEnum::INFECT)
            ->setTypes([ActionTypeEnum::ACTION_AGGRESSIVE])
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setActionCost(1)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::COVERT);

        $manager->persist($infectAction);

        $spreadFireAction = new Action();
        $spreadFireAction
            ->setName(ActionEnum::SPREAD_FIRE)
            ->setActionName(ActionEnum::SPREAD_FIRE)
            ->setScope(ActionScopeEnum::SELF)
            ->setActionCost(4)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::SECRET);

        $manager->persist($spreadFireAction);

        $makeSickAction = new Action();
        $makeSickAction
            ->setName(ActionEnum::MAKE_SICK)
            ->setActionName(ActionEnum::MAKE_SICK)
            ->setTypes([ActionTypeEnum::ACTION_AGGRESSIVE])
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setActionCost(1)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::COVERT);

        $manager->persist($makeSickAction);

        $fakeDiseaseAction = new Action();
        $fakeDiseaseAction
            ->setName(ActionEnum::FAKE_DISEASE)
            ->setActionName(ActionEnum::FAKE_DISEASE)
            ->setScope(ActionScopeEnum::SELF)
            ->setActionCost(1)
            ->setDirtyRate(60)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::SECRET);

        $manager->persist($fakeDiseaseAction);

        $screwTalkieAction = new Action();
        $screwTalkieAction
            ->setName(ActionEnum::SCREW_TALKIE)
            ->setActionName(ActionEnum::SCREW_TALKIE)
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setActionCost(3)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::COVERT);

        $manager->persist($screwTalkieAction);

        $phagocyteAction = new Action();
        $phagocyteAction
            ->setName(ActionEnum::PHAGOCYTE)
            ->setActionName(ActionEnum::PHAGOCYTE)
            ->setScope(ActionScopeEnum::SELF)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE);

        $manager->persist($phagocyteAction);

        $manager->flush();

        $this->addReference(self::EXTRACT_SPORE, $extractSporeAction);
        $this->addReference(self::INFECT_PLAYER, $infectAction);
        $this->addReference(self::SPREAD_FIRE, $spreadFireAction);
        $this->addReference(self::MAKE_SICK, $makeSickAction);
        $this->addReference(self::FAKE_DISEASE, $fakeDiseaseAction);
        $this->addReference(self::SCREW_TALKIE, $screwTalkieAction);
        $this->addReference(self::PHAGOCYTE, $phagocyteAction);
    }
}
