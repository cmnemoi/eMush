<?php

namespace Mush\Tests\unit\Modifier\Service;

use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Action\Event\ActionVariableEvent;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Event\VariableEventInterface;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Enum\ModifierTargetEnum;
use Mush\Modifier\Enum\VariableModifierModeEnum;
use Mush\Modifier\Service\EventModifierService;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Status\Entity\Attempt;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\StatusEnum;
use PHPUnit\Framework\TestCase;

class EventModifierServiceTest extends TestCase
{
    private EventModifierService $service;

    /**
     * @before
     */
    public function before()
    {
        $this->service = new EventModifierService();
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testModifiedValueFormula()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);
        $player = new Player();
        $player->setDaedalus($daedalus)->setPlace($room);

        $action = new Action();
        $action
            ->setActionName('action')
            ->setTypes(['type1', 'type2'])
            ->setMovementCost(1)
            ->setSuccessRate(50)
        ;

        // multiplicative
        $event = new ActionVariableEvent($action, ActionVariableEnum::PERCENTAGE_SUCCESS, 50, $player, null);
        $modifierConfig1 = new VariableEventModifierConfig('unitTestVariableEventModifier');
        $modifierConfig1
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
            ->setTargetEvent('action')
            ->setTargetVariable(ModifierTargetEnum::PERCENTAGE)
            ->setDelta(1.5)
            ->setMode(VariableModifierModeEnum::MULTIPLICATIVE)
        ;
        $modifier1 = new GameModifier($daedalus, $modifierConfig1);
        $modifierCollection = new ModifierCollection([$modifier1]);

        $modifiedEvent = $this->service->applyVariableModifiers($modifierCollection, $event);

        $this->assertInstanceOf(ActionVariableEvent::class, $modifiedEvent);
        $this->assertEquals(ActionVariableEnum::PERCENTAGE_SUCCESS, $modifiedEvent->getVariableName());
        $this->assertEquals(75, $modifiedEvent->getQuantity());

        // multiplicative and additive
        $event = new ActionVariableEvent($action, ActionVariableEnum::PERCENTAGE_SUCCESS, 50, $player, null);
        $modifierConfig2 = new VariableEventModifierConfig('unitTestVariableEventModifier');
        $modifierConfig2
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
            ->setTargetEvent('action')
            ->setTargetVariable(ModifierTargetEnum::PERCENTAGE)
            ->setDelta(10)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
        ;
        $modifier2 = new GameModifier($daedalus, $modifierConfig2);
        $modifierCollection->add($modifier2);

        $modifiedEvent = $this->service->applyVariableModifiers($modifierCollection, $event);

        $this->assertInstanceOf(ActionVariableEvent::class, $modifiedEvent);
        $this->assertEquals(ActionVariableEnum::PERCENTAGE_SUCCESS, $modifiedEvent->getVariableName());
        $this->assertEquals(85, $modifiedEvent->getQuantity());

        // add attempt
        $event = new ActionVariableEvent($action, ActionVariableEnum::PERCENTAGE_SUCCESS, 50, $player, null);
        $statusConfig = new ChargeStatusConfig();
        $statusConfig->setStatusName(StatusEnum::ATTEMPT);
        $attempt = new Attempt($player, $statusConfig);
        $attempt->setCharge(1);
        $attempt->setAction('action');

        $modifiedEvent = $this->service->applyVariableModifiers($modifierCollection, $event);

        $this->assertInstanceOf(ActionVariableEvent::class, $modifiedEvent);
        $this->assertEquals(ActionVariableEnum::PERCENTAGE_SUCCESS, $modifiedEvent->getVariableName());
        $this->assertEquals(intval(50 * 1.25 ** 1 * 1.5 + 10), $modifiedEvent->getQuantity());

        // More attempts
        $event = new ActionVariableEvent($action, ActionVariableEnum::PERCENTAGE_SUCCESS, 50, $player, null);
        $attempt->setCharge(3);

        $modifiedEvent = $this->service->applyVariableModifiers($modifierCollection, $event);

        $this->assertInstanceOf(ActionVariableEvent::class, $modifiedEvent);
        $this->assertEquals(ActionVariableEnum::PERCENTAGE_SUCCESS, $modifiedEvent->getVariableName());
        $this->assertEquals(intval(50 * 1.25 ** 3 * 1.5 + 10), $modifiedEvent->getQuantity());

        // Set Value
        $event = new ActionVariableEvent($action, ActionVariableEnum::PERCENTAGE_SUCCESS, 50, $player, null);
        $modifierConfig3 = new VariableEventModifierConfig('unitTestVariableEventModifier');
        $modifierConfig3
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
            ->setTargetEvent('action')
            ->setTargetVariable(ActionVariableEnum::PERCENTAGE_SUCCESS)
            ->setDelta(10)
            ->setMode(VariableModifierModeEnum::SET_VALUE)
        ;
        $modifier3 = new GameModifier($daedalus, $modifierConfig3);
        $modifierCollection->add($modifier3);

        $modifiedEvent = $this->service->applyVariableModifiers($modifierCollection, $event);

        $this->assertInstanceOf(ActionVariableEvent::class, $modifiedEvent);
        $this->assertEquals(ActionVariableEnum::PERCENTAGE_SUCCESS, $modifiedEvent->getVariableName());
        $this->assertEquals(intval(10), $modifiedEvent->getQuantity());
    }

    public function testApplyModifierNoActionPercentage()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);
        $player = new Player();
        $player->setDaedalus($daedalus)->setPlace($room);

        $action = new Action();
        $action
            ->setActionName('action')
            ->setTypes(['type1', 'type2'])
            ->setMovementCost(1)
            ->setSuccessRate(50)
        ;
        $event = new ActionVariableEvent($action, PlayerVariableEnum::MOVEMENT_POINT, 50, $player, null);

        // multiplicative
        $modifierConfig1 = new VariableEventModifierConfig('unitTestVariableEventModifier');
        $modifierConfig1
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
            ->setTargetEvent(ActionVariableEvent::APPLY_COST)
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(1.5)
            ->setMode(VariableModifierModeEnum::MULTIPLICATIVE)
        ;
        $modifier1 = new GameModifier($daedalus, $modifierConfig1);
        $modifierCollection = new ModifierCollection([$modifier1]);

        // add attempt
        $statusConfig = new ChargeStatusConfig();
        $statusConfig->setStatusName(StatusEnum::ATTEMPT);
        $attempt = new Attempt($player, $statusConfig);
        $attempt->setCharge(1);
        $attempt->setAction('action');

        $modifiedEvent = $this->service->applyVariableModifiers($modifierCollection, $event);

        $this->assertInstanceOf(ActionVariableEvent::class, $modifiedEvent);
        $this->assertEquals(PlayerVariableEnum::MOVEMENT_POINT, $modifiedEvent->getVariableName());
        $this->assertEquals(intval(75), $modifiedEvent->getQuantity());
    }

    public function testGetEventModifiedValueWithChangeInSign()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);
        $playerConfig = new CharacterConfig();
        $player = new Player();
        $player->setDaedalus($daedalus)->setPlace($room)->setPlayerVariables($playerConfig);

        $event = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::MOVEMENT_POINT,
            2,
            [VariableEventInterface::CHANGE_VARIABLE],
            new \DateTime()
        );
        $modifierConfig1 = new VariableEventModifierConfig('unitTestVariableEventModifier');
        $modifierConfig1
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
            ->setTargetEvent(VariableEventInterface::CHANGE_VARIABLE)
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(-6)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
        ;
        $modifier1 = new GameModifier($daedalus, $modifierConfig1);
        $modifierCollection = new ModifierCollection([$modifier1]);

        $modifiedEvent = $this->service->applyVariableModifiers($modifierCollection, $event);

        $this->assertInstanceOf(PlayerVariableEvent::class, $modifiedEvent);
        $this->assertEquals(0, $modifiedEvent->getQuantity());
    }
}
