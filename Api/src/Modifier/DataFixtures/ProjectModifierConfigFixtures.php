<?php

declare(strict_types=1);

namespace Mush\Modifier\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Game\Entity\VariableEventConfig;
use Mush\Game\Event\VariableEventInterface;
use Mush\Modifier\Entity\Config\DirectModifierConfig;
use Mush\Modifier\Entity\Config\TriggerEventModifierConfig;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Enum\ModifierPriorityEnum;
use Mush\Modifier\Enum\VariableModifierModeEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Status\Enum\DaedalusStatusEnum;

final class ProjectModifierConfigFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $trailReducerModifier = new VariableEventModifierConfig('modifier_for_daedalus_-25percentage_following_hunters_on_daedalus_travel');
        $trailReducerModifier
            ->setTargetVariable(DaedalusStatusEnum::FOLLOWING_HUNTERS)
            ->setDelta(0.75)
            ->setMode(VariableModifierModeEnum::MULTIPLICATIVE)
            ->setTargetEvent(VariableEventInterface::CHANGE_VARIABLE)
            ->setPriority(ModifierPriorityEnum::MULTIPLICATIVE_MODIFIER_VALUE)
            ->setTagConstraints([])
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS);

        $manager->persist($trailReducerModifier);
        $this->addReference($trailReducerModifier->getName(), $trailReducerModifier);

        $cpuOverclock = new VariableEventModifierConfig('modifier_for_daedalus_-1actionPoint_on_action_analyze_planet');
        $cpuOverclock
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setTargetEvent(VariableEventInterface::CHANGE_VARIABLE)
            ->setPriority(ModifierPriorityEnum::MULTIPLICATIVE_MODIFIER_VALUE)
            ->setTagConstraints([])
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS);
        $manager->persist($cpuOverclock);
        $this->addReference($cpuOverclock->getName(), $cpuOverclock);

        /** @var VariableEventConfig $eventConfig */
        $eventConfig = $this->getReference('set.value_daedalus_shield_50');
        $plasmaShieldInitModifier = new DirectModifierConfig('modifier_for_daedalus_set_daedalus_shield_to_50');
        $plasmaShieldInitModifier
            ->setRevertOnRemove(true)
            ->setTriggeredEvent($eventConfig)
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS);

        $manager->persist($plasmaShieldInitModifier);
        $this->addReference($plasmaShieldInitModifier->getName(), $plasmaShieldInitModifier);

        /** @var VariableEventConfig $eventConfig */
        $eventConfig = $this->getReference('change.variable_daedalus_shield_+5');

        $plasmaShieldNewCycleModifier = new TriggerEventModifierConfig('modifier_for_daedalus_+5shield_on_new_cycle');
        $plasmaShieldNewCycleModifier
            ->setTriggeredEvent($eventConfig)
            ->setTargetEvent(DaedalusCycleEvent::DAEDALUS_NEW_CYCLE)
            ->setPriority(ModifierPriorityEnum::AFTER_INITIAL_EVENT)
            ->setApplyOnTarget(true)
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS);

        $manager->persist($plasmaShieldNewCycleModifier);
        $this->addReference($plasmaShieldNewCycleModifier->getName(), $plasmaShieldNewCycleModifier);

        $manager->flush();
    }
}
