<?php

namespace Mush\Modifier\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionTypeEnum;
use Mush\Modifier\Entity\ModifierConfig;
use Mush\Modifier\Enum\ModifierModeEnum;
use Mush\Modifier\Enum\ModifierReachEnum;
use Mush\Modifier\Enum\ModifierScopeEnum;
use Mush\Modifier\Enum\ModifierTargetEnum;

class StatusModifierConfigFixtures extends Fixture
{
    public const FROZEN_MODIFIER = 'frozen_modifier';
    public const DISABLED_MODIFIER = 'disabled_modifier';
    public const PACIFIST_MODIFIER = 'pacifist_modifier';

    public function load(ObjectManager $manager): void
    {
        $frozenModifier = new ModifierConfig();

        $frozenModifier
            ->setScope(ActionEnum::CONSUME)
            ->setTarget(ModifierTargetEnum::ACTION_POINT)
            ->setDelta(1)
            ->setReach(ModifierReachEnum::EQUIPMENT)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;
        $manager->persist($frozenModifier);

        $disabledModifier = new ModifierConfig();
        $disabledModifier
            ->setScope(ModifierScopeEnum::EVENT_ACTION_MOVEMENT_CONVERSION)
            ->setTarget(ModifierTargetEnum::MOVEMENT_POINT)
            ->setDelta(-2)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;
        $manager->persist($disabledModifier);

        $pacifistModifier = new ModifierConfig();
        $pacifistModifier
            ->setScope(ActionTypeEnum::ACTION_AGGRESSIVE)
            ->setTarget(ModifierTargetEnum::ACTION_POINT)
            ->setDelta(2)
            ->setReach(ModifierReachEnum::PLACE)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;
        $manager->persist($pacifistModifier);

        $this->addReference(self::FROZEN_MODIFIER, $frozenModifier);
        $this->addReference(self::DISABLED_MODIFIER, $disabledModifier);
        $this->addReference(self::PACIFIST_MODIFIER, $pacifistModifier);
    }
}
