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

class ModifierCollectionTest extends TestCase
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

        $this->assertCount(4, $newCollection);
    }

    public function testGetModifierFromConfig()
    {
        $player = new Player();
        $modifierConfig1 = new VariableEventModifierConfig('unitTestVariableEventModifier');
        $modifierConfig2 = new VariableEventModifierConfig('unitTestVariableEventModifier');

        $modifier1 = new GameModifier($player, $modifierConfig1);
        $modifier2 = new GameModifier($player, $modifierConfig1);
        $modifier3 = new GameModifier(new Daedalus(), $modifierConfig1);
        $modifier4 = new GameModifier(new Place(), $modifierConfig2);

        $modifierCollection1 = new ModifierCollection([$modifier1, $modifier2, $modifier3, $modifier4]);

        $result = $modifierCollection1->getModifierFromConfig($modifierConfig2);

        $this->assertEquals($modifier4, $result);
    }

    public function testGetEventModifiersWithoutTags()
    {
        $time = new \DateTime();
        $player = new Player();
        $modifierConfig1 = new VariableEventModifierConfig('unitTestVariableEventModifier');
        $modifierConfig1
            ->setTargetVariable(DaedalusVariableEnum::FUEL)
            ->setTargetEvent(VariableEventInterface::CHANGE_VARIABLE)
        ;
        $modifierConfig2 = new VariableEventModifierConfig('unitTestVariableEventModifier');
        $modifierConfig2
            ->setTargetVariable(DaedalusVariableEnum::HULL)
            ->setTargetEvent(VariableEventInterface::CHANGE_VARIABLE)
        ;
        $modifierConfig3 = new TriggerEventModifierConfig('unitTestTriggerEventModifier');
        $modifierConfig3
            ->setTargetEvent(VariableEventInterface::CHANGE_VARIABLE)
        ;
        $modifierConfig4 = new TriggerEventModifierConfig('unitTestTriggerEventModifier');
        $modifierConfig4
            ->setTargetEvent(VariableEventInterface::ROLL_PERCENTAGE)
        ;

        $modifier1 = new GameModifier($player, $modifierConfig1);
        $modifier2 = new GameModifier($player, $modifierConfig2);
        $modifier3 = new GameModifier(new Daedalus(), $modifierConfig3);
        $modifier4 = new GameModifier(new Place(), $modifierConfig4);

        $modifierCollection = new ModifierCollection([$modifier1, $modifier2, $modifier3, $modifier4]);

        // first try an event that is not a variableEventInterface
        $event = new AbstractGameEvent([], $time);
        $event->setEventName(VariableEventInterface::CHANGE_VARIABLE);

        $result = $modifierCollection->getEventModifiers($event, [ModifierPriorityEnum::BEFORE_INITIAL_EVENT]);

        $this->assertCount(1, $result);
        $this->assertNotContains($modifier1, $result);
        $this->assertNotContains($modifier2, $result);
        $this->assertContains($modifier3, $result);
        $this->assertNotContains($modifier4, $result);

        // now a variableEventInterface
        $event = new DaedalusVariableEvent(new Daedalus(), DaedalusVariableEnum::FUEL, 2, [], $time);
        $event->setEventName(VariableEventInterface::CHANGE_VARIABLE);

        $result = $modifierCollection->getEventModifiers($event, [ModifierPriorityEnum::BEFORE_INITIAL_EVENT, ModifierPriorityEnum::ADDITIVE_MODIFIER_VALUE]);

        $this->assertCount(2, $result);
        $this->assertContains($modifier1, $result);
        $this->assertNotContains($modifier2, $result);
        $this->assertContains($modifier3, $result);
        $this->assertNotContains($modifier4, $result);
    }

    public function testGetEventModifiersWithTags()
    {
        $time = new \DateTime();
        $player = new Player();
        $modifierConfig1 = new TriggerEventModifierConfig('unitTestTriggerEventModifier');
        $modifierConfig1
            ->setTargetEvent(VariableEventInterface::CHANGE_VARIABLE)
            ->setTagConstraints([
                ActionEnum::ANATHEMA => ModifierRequirementEnum::ALL_TAGS,
                ActionEnum::AUTO_DESTROY => ModifierRequirementEnum::ALL_TAGS,
            ])
        ;
        $modifierConfig2 = new TriggerEventModifierConfig('unitTestTriggerEventModifier');
        $modifierConfig2
            ->setTargetEvent(VariableEventInterface::CHANGE_VARIABLE)
            ->setTagConstraints([
                ActionEnum::ANATHEMA => ModifierRequirementEnum::ANY_TAGS,
                ActionEnum::AUTO_DESTROY => ModifierRequirementEnum::ANY_TAGS,
            ])
        ;
        $modifierConfig3 = new TriggerEventModifierConfig('unitTestTriggerEventModifier');
        $modifierConfig3
            ->setTargetEvent(VariableEventInterface::CHANGE_VARIABLE)
            ->setTagConstraints([
                ActionEnum::ANATHEMA => ModifierRequirementEnum::NONE_TAGS,
            ])
        ;
        $modifierConfig4 = new TriggerEventModifierConfig('unitTestTriggerEventModifier');
        $modifierConfig4
            ->setTargetEvent(VariableEventInterface::CHANGE_VARIABLE)
            ->setTagConstraints([
                ActionEnum::ANATHEMA => ModifierRequirementEnum::ALL_TAGS,
                ActionEnum::AUTO_DESTROY => ModifierRequirementEnum::ALL_TAGS,
                ActionEnum::ATTACK => ModifierRequirementEnum::ANY_TAGS,
                ActionEnum::CEASEFIRE => ModifierRequirementEnum::ANY_TAGS,
                StatusEnum::FIRE => ModifierRequirementEnum::NONE_TAGS,
            ])
        ;

        $modifier1 = new GameModifier($player, $modifierConfig1);
        $modifier2 = new GameModifier($player, $modifierConfig2);
        $modifier3 = new GameModifier(new Daedalus(), $modifierConfig3);
        $modifier4 = new GameModifier(new Place(), $modifierConfig4);

        $modifierCollection = new ModifierCollection([$modifier1, $modifier2, $modifier3, $modifier4]);

        // No Tags
        $event = new AbstractGameEvent([], $time);
        $event->setEventName(VariableEventInterface::CHANGE_VARIABLE);
        $result = $modifierCollection->getEventModifiers($event, [ModifierPriorityEnum::BEFORE_INITIAL_EVENT]);

        $this->assertCount(1, $result);
        $this->assertNotContains($modifier1, $result);
        $this->assertNotContains($modifier2, $result);
        $this->assertContains($modifier3, $result);
        $this->assertNotContains($modifier4, $result);

        // Anathen tag
        $event = new AbstractGameEvent([ActionEnum::ANATHEMA], $time);
        $event->setEventName(VariableEventInterface::CHANGE_VARIABLE);
        $result = $modifierCollection->getEventModifiers($event, [ModifierPriorityEnum::BEFORE_INITIAL_EVENT]);

        $this->assertCount(1, $result);
        $this->assertNotContains($modifier1, $result);
        $this->assertContains($modifier2, $result);
        $this->assertNotContains($modifier3, $result);
        $this->assertNotContains($modifier4, $result);

        // anathen and auto destroy
        $event = new AbstractGameEvent([ActionEnum::ANATHEMA, ActionEnum::AUTO_DESTROY], $time);
        $event->setEventName(VariableEventInterface::CHANGE_VARIABLE);
        $result = $modifierCollection->getEventModifiers($event, [ModifierPriorityEnum::BEFORE_INITIAL_EVENT]);

        $this->assertCount(2, $result);
        $this->assertContains($modifier1, $result);
        $this->assertContains($modifier2, $result);
        $this->assertNotContains($modifier3, $result);
        $this->assertNotContains($modifier4, $result);

        // Auto destroy
        $event = new AbstractGameEvent([ActionEnum::AUTO_DESTROY], $time);
        $event->setEventName(VariableEventInterface::CHANGE_VARIABLE);
        $result = $modifierCollection->getEventModifiers($event, [ModifierPriorityEnum::BEFORE_INITIAL_EVENT]);

        $this->assertCount(2, $result);
        $this->assertNotContains($modifier1, $result);
        $this->assertContains($modifier2, $result);
        $this->assertContains($modifier3, $result);
        $this->assertNotContains($modifier4, $result);

        // 3 tags
        $event = new AbstractGameEvent([ActionEnum::AUTO_DESTROY, ActionEnum::ANATHEMA, ActionEnum::CEASEFIRE], $time);
        $event->setEventName(VariableEventInterface::CHANGE_VARIABLE);
        $result = $modifierCollection->getEventModifiers($event, [ModifierPriorityEnum::BEFORE_INITIAL_EVENT]);

        $this->assertCount(3, $result);
        $this->assertContains($modifier1, $result);
        $this->assertContains($modifier2, $result);
        $this->assertNotContains($modifier3, $result);
        $this->assertContains($modifier4, $result);

        // 4 tags
        $event = new AbstractGameEvent([
            ActionEnum::AUTO_DESTROY,
            ActionEnum::ANATHEMA,
            ActionEnum::CEASEFIRE,
            StatusEnum::FIRE,
        ], $time);
        $event->setEventName(VariableEventInterface::CHANGE_VARIABLE);
        $result = $modifierCollection->getEventModifiers($event, [ModifierPriorityEnum::BEFORE_INITIAL_EVENT]);

        $this->assertCount(2, $result);
        $this->assertContains($modifier1, $result);
        $this->assertContains($modifier2, $result);
        $this->assertNotContains($modifier3, $result);
        $this->assertNotContains($modifier4, $result);
    }
}
