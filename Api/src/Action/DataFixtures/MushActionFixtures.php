<?php

namespace Mush\Action\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\ConfigData\ActionData;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionHolderEnum;
use Mush\Action\Enum\ActionRangeEnum;
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

    public function load(ObjectManager $manager): void
    {
        $extractSporeAction = new ActionConfig();
        $extractSporeAction
            ->setName(ActionEnum::EXTRACT_SPORE->value)
            ->setActionName(ActionEnum::EXTRACT_SPORE)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::PLAYER)
            ->setActionCost(2)
            ->setDirtyRate(100)
            ->makeSuperDirty()
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::SECRET);

        $manager->persist($extractSporeAction);

        $infectAction = new ActionConfig();
        $infectAction
            ->setName(ActionEnum::INFECT->value)
            ->setActionName(ActionEnum::INFECT)
            ->setTypes([ActionTypeEnum::ACTION_AGGRESSIVE])
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::OTHER_PLAYER)
            ->setActionCost(1)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::COVERT);

        $manager->persist($infectAction);

        $spreadFireAction = new ActionConfig();
        $spreadFireAction
            ->setName(ActionEnum::SPREAD_FIRE->value)
            ->setActionName(ActionEnum::SPREAD_FIRE)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::PLAYER)
            ->setActionCost(4)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::SECRET);

        $manager->persist($spreadFireAction);

        $makeSickAction = new ActionConfig();
        $makeSickAction
            ->setName(ActionEnum::MAKE_SICK->value)
            ->setActionName(ActionEnum::MAKE_SICK)
            ->setTypes([ActionTypeEnum::ACTION_AGGRESSIVE])
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::OTHER_PLAYER)
            ->setActionCost(1)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::COVERT);

        $manager->persist($makeSickAction);

        $fakeDiseaseAction = new ActionConfig();
        $fakeDiseaseAction
            ->setName(ActionEnum::FAKE_DISEASE->value)
            ->setActionName(ActionEnum::FAKE_DISEASE)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::PLAYER)
            ->setActionCost(1)
            ->setDirtyRate(60)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::SECRET);

        $manager->persist($fakeDiseaseAction);

        $screwTalkieAction = new ActionConfig();
        $screwTalkieAction
            ->setName(ActionEnum::SCREW_TALKIE->value)
            ->setActionName(ActionEnum::SCREW_TALKIE)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::OTHER_PLAYER)
            ->setActionCost(3)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::COVERT);

        $manager->persist($screwTalkieAction);

        $phagocyteAction = new ActionConfig();
        $phagocyteAction
            ->setName(ActionEnum::PHAGOCYTE->value)
            ->setActionName(ActionEnum::PHAGOCYTE)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::PLAYER)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE);

        $manager->persist($phagocyteAction);

        $trapClosetAction = new ActionConfig();
        $trapClosetAction
            ->setName(ActionEnum::TRAP_CLOSET->value)
            ->setActionName(ActionEnum::TRAP_CLOSET)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::PLAYER)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::SECRET)
            ->setActionCost(1);

        $manager->persist($trapClosetAction);

        $exchangeBoodyAction = ActionConfig::fromConfigData(
            ActionData::getByName(ActionEnum::EXCHANGE_BODY)
        );
        $manager->persist($exchangeBoodyAction);

        $manager->flush();

        $this->addReference(self::EXTRACT_SPORE, $extractSporeAction);
        $this->addReference(self::INFECT_PLAYER, $infectAction);
        $this->addReference(self::SPREAD_FIRE, $spreadFireAction);
        $this->addReference(self::MAKE_SICK, $makeSickAction);
        $this->addReference(self::FAKE_DISEASE, $fakeDiseaseAction);
        $this->addReference(self::SCREW_TALKIE, $screwTalkieAction);
        $this->addReference(ActionEnum::PHAGOCYTE->value, $phagocyteAction);
        $this->addReference(ActionEnum::TRAP_CLOSET->value, $trapClosetAction);
        $this->addReference(ActionEnum::EXCHANGE_BODY->value, $exchangeBoodyAction);
    }
}
