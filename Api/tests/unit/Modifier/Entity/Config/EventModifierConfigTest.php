<?php

namespace Mush\Test\Modifier\Entity\Config;

use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionVariableEvent;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Game\Event\VariableEventInterface;
use Mush\Modifier\Entity\Config\TriggerEventModifierConfig;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use PHPUnit\Framework\TestCase;

class EventModifierConfigTest extends TestCase
{
    public function testDoModifierApplyTriggerModifier()
    {
        $modifier = new TriggerEventModifierConfig('unitTestTriggerEventModifier');
        $modifier
            ->setTargetEvent(ActionVariableEvent::APPLY_COST)
            ->setTagConstraints([
                ActionEnum::CHECK_INFECTION => ModifierRequirementEnum::NONE_TAGS,
                ActionEnum::ANATHEM => ModifierRequirementEnum::ALL_TAGS,
                ActionVariableEvent::MOVEMENT_CONVERSION => ModifierRequirementEnum::ALL_TAGS,
                ModifierNameEnum::APRON_MODIFIER => ModifierRequirementEnum::ANY_TAGS,
                ActionEnum::SHOWER => ModifierRequirementEnum::ANY_TAGS,
            ])
        ;

        $event = new AbstractGameEvent([], new \DateTime());

        $event->setEventName(VariableEventInterface::ROLL_PERCENTAGE);
        $this->assertFalse($modifier->doModifierApplies($event));

        $event->setEventName(ActionVariableEvent::APPLY_COST);
        $this->assertFalse($modifier->doModifierApplies($event));

        $event->addTag(ModifierNameEnum::APRON_MODIFIER);
        $this->assertFalse($modifier->doModifierApplies($event));

        $event->addTag(ActionVariableEvent::MOVEMENT_CONVERSION);
        $this->assertFalse($modifier->doModifierApplies($event));

        $event->addTag(ActionEnum::ANATHEM);
        $this->assertTrue($modifier->doModifierApplies($event));

        $event->addTag(ActionEnum::CHECK_INFECTION);
        $this->assertFalse($modifier->doModifierApplies($event));
    }

    public function testDoModifierApplyVariableModifier()
    {
        $modifier = new VariableEventModifierConfig('unitTestVariableEventModifier');
        $modifier
            ->setTargetEvent(ActionVariableEvent::APPLY_COST)
            ->setTargetVariable(DaedalusVariableEnum::FUEL)
        ;
        $action = new Action();
        $action->setActionName('test');

        $event = new AbstractGameEvent([], new \DateTime());

        $event->setEventName(VariableEventInterface::ROLL_PERCENTAGE);
        $this->assertFalse($modifier->doModifierApplies($event));

        $event->setEventName(ActionVariableEvent::APPLY_COST);
        $this->assertFalse($modifier->doModifierApplies($event));

        $event = new ActionVariableEvent($action, PlayerVariableEnum::ACTION_POINT, 2, new Player(), null);
        $event->setEventName(ActionVariableEvent::APPLY_COST);
        $this->assertFalse($modifier->doModifierApplies($event));

        $event = new ActionVariableEvent($action, DaedalusVariableEnum::FUEL, 2, new Player(), null);
        $event->setEventName(ActionVariableEvent::APPLY_COST);
        $this->assertTrue($modifier->doModifierApplies($event));
    }
}
