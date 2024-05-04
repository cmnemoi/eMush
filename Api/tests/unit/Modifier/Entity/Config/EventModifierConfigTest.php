<?php

namespace Mush\Tests\unit\Modifier\Entity\Config;

use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionVariableEvent;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Game\Event\VariableEventInterface;
use Mush\Modifier\Entity\Config\EventModifierConfig;
use Mush\Modifier\Entity\Config\TriggerEventModifierConfig;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Modifier\Enum\ModifierPriorityEnum;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class EventModifierConfigTest extends TestCase
{
    public function testDoModifierApplyTriggerModifier()
    {
        $modifier = new TriggerEventModifierConfig('unitTestTriggerEventModifier');
        $modifier
            ->setTargetEvent(ActionVariableEvent::APPLY_COST)
            ->setTagConstraints([
                ActionEnum::CHECK_INFECTION => ModifierRequirementEnum::NONE_TAGS,
                ActionEnum::ANATHEMA => ModifierRequirementEnum::ALL_TAGS,
                ActionEnum::CONVERT_ACTION_TO_MOVEMENT => ModifierRequirementEnum::ALL_TAGS,
                ModifierNameEnum::APRON_MODIFIER => ModifierRequirementEnum::ANY_TAGS,
                ActionEnum::SHOWER => ModifierRequirementEnum::ANY_TAGS,
            ]);

        $event = new AbstractGameEvent([], new \DateTime());

        $event->setEventName(VariableEventInterface::ROLL_PERCENTAGE);
        self::assertFalse($modifier->doModifierApplies($event));

        $event->setEventName(ActionVariableEvent::APPLY_COST);
        self::assertFalse($modifier->doModifierApplies($event));

        $event->addTag(ModifierNameEnum::APRON_MODIFIER);
        self::assertFalse($modifier->doModifierApplies($event));

        $event->addTag(ActionEnum::CONVERT_ACTION_TO_MOVEMENT);
        self::assertFalse($modifier->doModifierApplies($event));

        $event->addTag(ActionEnum::ANATHEMA);
        self::assertTrue($modifier->doModifierApplies($event));

        $event->addTag(ActionEnum::CHECK_INFECTION);
        self::assertFalse($modifier->doModifierApplies($event));
    }

    public function testDoModifierApplyVariableModifier()
    {
        $modifier = new VariableEventModifierConfig('unitTestVariableEventModifier');
        $modifier
            ->setTargetEvent(ActionVariableEvent::APPLY_COST)
            ->setTargetVariable(DaedalusVariableEnum::FUEL);
        $action = new ActionConfig();
        $action->setActionName('test');
        $player = new Player();
        $player->setDaedalus(new Daedalus());

        $event = new AbstractGameEvent([], new \DateTime());

        $event->setEventName(VariableEventInterface::ROLL_PERCENTAGE);
        self::assertFalse($modifier->doModifierApplies($event));

        $event->setEventName(ActionVariableEvent::APPLY_COST);
        self::assertFalse($modifier->doModifierApplies($event));

        $event = new ActionVariableEvent($action, PlayerVariableEnum::ACTION_POINT, 2, $player, null);
        $event->setEventName(ActionVariableEvent::APPLY_COST);
        self::assertFalse($modifier->doModifierApplies($event));

        $event = new ActionVariableEvent($action, DaedalusVariableEnum::FUEL, 2, $player, null);
        $event->setEventName(ActionVariableEvent::APPLY_COST);
        self::assertTrue($modifier->doModifierApplies($event));
    }

    public function testGetPriority()
    {
        $modifier = new EventModifierConfig('unitTestVariableEventModifier');
        $modifier
            ->setPriority(ModifierPriorityEnum::BEFORE_INITIAL_EVENT);

        self::assertSame(ModifierPriorityEnum::PRIORITY_MAP[ModifierPriorityEnum::BEFORE_INITIAL_EVENT], $modifier->getPriorityAsInteger());
        self::assertSame(ModifierPriorityEnum::BEFORE_INITIAL_EVENT, $modifier->getPriority());

        $modifier
            ->setPriority('4');
        self::assertSame(4, $modifier->getPriorityAsInteger());
        self::assertSame('4', $modifier->getPriority());
    }
}
