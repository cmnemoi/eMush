<?php

namespace Mush\Tests\functional\Game\Service;

use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Daedalus\Event\DaedalusVariableEvent;
use Mush\Game\Entity\VariableEventConfig;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Modifier\Entity\Config\TriggerEventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Event\ModifierEvent;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

class EventServiceCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function testIsInfiniteLoopCallEventAvoided(FunctionalTester $I)
    {
        $variableEventConfig = new VariableEventConfig();
        $variableEventConfig
            ->setEventName(VariableEventInterface::CHANGE_VARIABLE)
            ->setTargetVariable(DaedalusVariableEnum::HULL)
            ->setQuantity(-3)
            ->setName('event_config_test_infinite_loop')
            ->setVariableHolderClass(ModifierHolderClassEnum::DAEDALUS)
        ;

        $triggerModifierConfig = new TriggerEventModifierConfig('trigger_event_modifier_test_infinite_loop');
        $triggerModifierConfig
            ->setTargetEvent(VariableEventInterface::CHANGE_VARIABLE)
            ->setTriggeredEvent($variableEventConfig)
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
        ;

        $I->haveInRepository($variableEventConfig);
        $I->haveInRepository($triggerModifierConfig);

        $gameModifier = new GameModifier($this->daedalus, $triggerModifierConfig);
        $I->haveInRepository($gameModifier);

        $event = new DaedalusVariableEvent($this->daedalus, DaedalusVariableEnum::HULL, -5, [], new \DateTime());

        $hullInitial = $this->daedalus->getHull();

        $this->eventService->callEvent($event, VariableEventInterface::CHANGE_VARIABLE);

        $I->assertEquals($hullInitial - 8, $this->daedalus->getHull());
    }

    public function testIsInfiniteLoopCallEventAvoidedBis(FunctionalTester $I)
    {
        $variableEventConfig = new VariableEventConfig();
        $variableEventConfig
            ->setEventName(VariableEventInterface::CHANGE_VARIABLE)
            ->setTargetVariable(DaedalusVariableEnum::HULL)
            ->setQuantity(-3)
            ->setName('event_config_test_infinite_loop')
            ->setVariableHolderClass(ModifierHolderClassEnum::DAEDALUS)
        ;

        $triggerModifierConfig = new TriggerEventModifierConfig('trigger_event_modifier_test_infinite_loop');
        $triggerModifierConfig
            ->setTargetEvent(VariableEventInterface::CHANGE_VARIABLE)
            ->setTriggeredEvent($variableEventConfig)
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
        ;

        $triggerModifierConfig2 = new TriggerEventModifierConfig('trigger_event_modifier_test_infinite_loop_bis');
        $triggerModifierConfig2
            ->setTargetEvent(ModifierEvent::APPLY_MODIFIER)
            ->setTriggeredEvent($variableEventConfig)
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
        ;

        $I->haveInRepository($variableEventConfig);
        $I->haveInRepository($triggerModifierConfig);
        $I->haveInRepository($triggerModifierConfig2);

        $gameModifier = new GameModifier($this->daedalus, $triggerModifierConfig);
        $I->haveInRepository($gameModifier);

        $gameModifier2 = new GameModifier($this->daedalus, $triggerModifierConfig2);
        $I->haveInRepository($gameModifier2);

        $event = new DaedalusVariableEvent($this->daedalus, DaedalusVariableEnum::HULL, -5, [], new \DateTime());

        $hullInitial = $this->daedalus->getHull();

        $this->eventService->callEvent($event, VariableEventInterface::CHANGE_VARIABLE);

        $I->assertEquals($hullInitial - 8, $this->daedalus->getHull());
    }
}
