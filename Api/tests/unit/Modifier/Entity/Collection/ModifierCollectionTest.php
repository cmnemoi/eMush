<?php

namespace Mush\Tests\unit\Modifier\Entity\Collection;

use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Daedalus\Event\DaedalusVariableEvent;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Game\Event\VariableEventInterface;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\Config\TriggerEventModifierConfig;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Enum\ModifierPriorityEnum;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\StatusEnum;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ModifierCollectionTest extends TestCase
{
    public function testAddModifiers()
    {
        $player = new Player();
        $modifierConfig = new VariableEventModifierConfig('unitTestVariableEventModifier');

        $modifier1 = new GameModifier($player, $modifierConfig);
        $modifier2 = new GameModifier($player, $modifierConfig);
        $modifier3 = new GameModifier(new Daedalus(), $modifierConfig);
        $modifier4 = new GameModifier(new Place(), $modifierConfig);

        $modifierCollection1 = new ModifierCollection([$modifier1, $modifier2]);
        $modifierCollection2 = new ModifierCollection([$modifier3, $modifier4]);

        $newCollection = $modifierCollection1->addModifiers($modifierCollection2);

        self::assertCount(4, $newCollection);
    }

    public function testGetModifierFromConfig()
    {
        $player = new Player();
        $modifierConfig1 = new VariableEventModifierConfig('unitTestVariableEventModifier');
        $modifierConfig2 = new VariableEventModifierConfig('unitTestVariableEventModifier');

        $modifier1 = new GameModifier($player, $modifierConfig1);
        $modifier2 = new GameModifier($player, $modifierConfig1);
        $modifier3 = new GameModifier(new Daedalus(), $modifierConfig2);
        $modifier4 = new GameModifier(new Place(), $modifierConfig2);
        $modifier1->setModifierProvider($player);
        $modifier2->setModifierProvider($player);
        $modifier3->setModifierProvider(new Player());
        $modifier4->setModifierProvider($player);

        $modifierCollection1 = new ModifierCollection([$modifier1, $modifier2, $modifier3, $modifier4]);

        $result = $modifierCollection1->getModifierFromConfigAndProvider($modifierConfig2, $player);

        self::assertSame($modifier4, $result);
    }

    public function testGetEventModifiersWithoutTags()
    {
        $time = new \DateTime();
        $player = new Player();
        $modifierConfig1 = new VariableEventModifierConfig('unitTestVariableEventModifier');
        $modifierConfig1
            ->setTargetVariable(DaedalusVariableEnum::FUEL)
            ->setTargetEvent(VariableEventInterface::CHANGE_VARIABLE);
        $modifierConfig2 = new VariableEventModifierConfig('unitTestVariableEventModifier');
        $modifierConfig2
            ->setTargetVariable(DaedalusVariableEnum::HULL)
            ->setTargetEvent(VariableEventInterface::CHANGE_VARIABLE);
        $modifierConfig3 = new TriggerEventModifierConfig('unitTestTriggerEventModifier');
        $modifierConfig3
            ->setTargetEvent(VariableEventInterface::CHANGE_VARIABLE);
        $modifierConfig4 = new TriggerEventModifierConfig('unitTestTriggerEventModifier');
        $modifierConfig4
            ->setTargetEvent(VariableEventInterface::ROLL_PERCENTAGE);

        $modifier1 = new GameModifier($player, $modifierConfig1);
        $modifier2 = new GameModifier($player, $modifierConfig2);
        $modifier3 = new GameModifier(new Daedalus(), $modifierConfig3);
        $modifier4 = new GameModifier(new Place(), $modifierConfig4);

        $modifierCollection = new ModifierCollection([$modifier1, $modifier2, $modifier3, $modifier4]);

        // first try an event that is not a variableEventInterface
        $event = new AbstractGameEvent([], $time);
        $event->setEventName(VariableEventInterface::CHANGE_VARIABLE);

        $result = $modifierCollection->getEventModifiers($event, [ModifierPriorityEnum::BEFORE_INITIAL_EVENT]);

        self::assertCount(1, $result);
        self::assertNotContains($modifier1, $result);
        self::assertNotContains($modifier2, $result);
        self::assertContains($modifier3, $result);
        self::assertNotContains($modifier4, $result);

        // now a variableEventInterface
        $event = new DaedalusVariableEvent(new Daedalus(), DaedalusVariableEnum::FUEL, 2, [], $time);
        $event->setEventName(VariableEventInterface::CHANGE_VARIABLE);

        $result = $modifierCollection->getEventModifiers($event, [ModifierPriorityEnum::BEFORE_INITIAL_EVENT, ModifierPriorityEnum::ADDITIVE_MODIFIER_VALUE]);

        self::assertCount(2, $result);
        self::assertContains($modifier1, $result);
        self::assertNotContains($modifier2, $result);
        self::assertContains($modifier3, $result);
        self::assertNotContains($modifier4, $result);
    }

    public function testGetEventModifiersWithTags()
    {
        $time = new \DateTime();
        $player = new Player();
        $modifierConfig1 = new TriggerEventModifierConfig('unitTestTriggerEventModifier');
        $modifierConfig1
            ->setTargetEvent(VariableEventInterface::CHANGE_VARIABLE)
            ->setTagConstraints([
                ActionEnum::ANATHEMA->value => ModifierRequirementEnum::ALL_TAGS,
                ActionEnum::AUTO_DESTROY->value => ModifierRequirementEnum::ALL_TAGS,
            ]);
        $modifierConfig2 = new TriggerEventModifierConfig('unitTestTriggerEventModifier');
        $modifierConfig2
            ->setTargetEvent(VariableEventInterface::CHANGE_VARIABLE)
            ->setTagConstraints([
                ActionEnum::ANATHEMA->value => ModifierRequirementEnum::ANY_TAGS,
                ActionEnum::AUTO_DESTROY->value => ModifierRequirementEnum::ANY_TAGS,
            ]);
        $modifierConfig3 = new TriggerEventModifierConfig('unitTestTriggerEventModifier');
        $modifierConfig3
            ->setTargetEvent(VariableEventInterface::CHANGE_VARIABLE)
            ->setTagConstraints([
                ActionEnum::ANATHEMA->value => ModifierRequirementEnum::NONE_TAGS,
            ]);
        $modifierConfig4 = new TriggerEventModifierConfig('unitTestTriggerEventModifier');
        $modifierConfig4
            ->setTargetEvent(VariableEventInterface::CHANGE_VARIABLE)
            ->setTagConstraints([
                ActionEnum::ANATHEMA->value => ModifierRequirementEnum::ALL_TAGS,
                ActionEnum::AUTO_DESTROY->value => ModifierRequirementEnum::ALL_TAGS,
                ActionEnum::ATTACK->value => ModifierRequirementEnum::ANY_TAGS,
                ActionEnum::CEASEFIRE->value => ModifierRequirementEnum::ANY_TAGS,
                StatusEnum::FIRE => ModifierRequirementEnum::NONE_TAGS,
            ]);

        $modifier1 = new GameModifier($player, $modifierConfig1);
        $modifier2 = new GameModifier($player, $modifierConfig2);
        $modifier3 = new GameModifier(new Daedalus(), $modifierConfig3);
        $modifier4 = new GameModifier(new Place(), $modifierConfig4);

        $modifierCollection = new ModifierCollection([$modifier1, $modifier2, $modifier3, $modifier4]);

        // No Tags
        $event = new AbstractGameEvent([], $time);
        $event->setEventName(VariableEventInterface::CHANGE_VARIABLE);
        $result = $modifierCollection->getEventModifiers($event, [ModifierPriorityEnum::BEFORE_INITIAL_EVENT]);

        self::assertCount(1, $result);
        self::assertNotContains($modifier1, $result);
        self::assertNotContains($modifier2, $result);
        self::assertContains($modifier3, $result);
        self::assertNotContains($modifier4, $result);

        // Anathen tag
        $event = new AbstractGameEvent([ActionEnum::ANATHEMA->value], $time);
        $event->setEventName(VariableEventInterface::CHANGE_VARIABLE);
        $result = $modifierCollection->getEventModifiers($event, [ModifierPriorityEnum::BEFORE_INITIAL_EVENT]);

        self::assertCount(1, $result);
        self::assertNotContains($modifier1, $result);
        self::assertContains($modifier2, $result);
        self::assertNotContains($modifier3, $result);
        self::assertNotContains($modifier4, $result);

        // anathen and auto destroy
        $event = new AbstractGameEvent([ActionEnum::ANATHEMA->value, ActionEnum::AUTO_DESTROY->value], $time);
        $event->setEventName(VariableEventInterface::CHANGE_VARIABLE);
        $result = $modifierCollection->getEventModifiers($event, [ModifierPriorityEnum::BEFORE_INITIAL_EVENT]);

        self::assertCount(2, $result);
        self::assertContains($modifier1, $result);
        self::assertContains($modifier2, $result);
        self::assertNotContains($modifier3, $result);
        self::assertNotContains($modifier4, $result);

        // Auto destroy
        $event = new AbstractGameEvent([ActionEnum::AUTO_DESTROY->value], $time);
        $event->setEventName(VariableEventInterface::CHANGE_VARIABLE);
        $result = $modifierCollection->getEventModifiers($event, [ModifierPriorityEnum::BEFORE_INITIAL_EVENT]);

        self::assertCount(2, $result);
        self::assertNotContains($modifier1, $result);
        self::assertContains($modifier2, $result);
        self::assertContains($modifier3, $result);
        self::assertNotContains($modifier4, $result);

        // 3 tags
        $event = new AbstractGameEvent([ActionEnum::AUTO_DESTROY->value, ActionEnum::ANATHEMA->value, ActionEnum::CEASEFIRE->value], $time);
        $event->setEventName(VariableEventInterface::CHANGE_VARIABLE);
        $result = $modifierCollection->getEventModifiers($event, [ModifierPriorityEnum::BEFORE_INITIAL_EVENT]);

        self::assertCount(3, $result);
        self::assertContains($modifier1, $result);
        self::assertContains($modifier2, $result);
        self::assertNotContains($modifier3, $result);
        self::assertContains($modifier4, $result);

        // 4 tags
        $event = new AbstractGameEvent([
            ActionEnum::AUTO_DESTROY->value,
            ActionEnum::ANATHEMA->value,
            ActionEnum::CEASEFIRE->value,
            StatusEnum::FIRE,
        ], $time);
        $event->setEventName(VariableEventInterface::CHANGE_VARIABLE);
        $result = $modifierCollection->getEventModifiers($event, [ModifierPriorityEnum::BEFORE_INITIAL_EVENT]);

        self::assertCount(2, $result);
        self::assertContains($modifier1, $result);
        self::assertContains($modifier2, $result);
        self::assertNotContains($modifier3, $result);
        self::assertNotContains($modifier4, $result);
    }
}
