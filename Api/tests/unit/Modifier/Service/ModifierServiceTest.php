<?php

namespace Mush\Test\Modifier\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionVariables;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Entity\ModifierConfig;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Enum\ModifierModeEnum;
use Mush\Modifier\Enum\ModifierScopeEnum;
use Mush\Modifier\Enum\ModifierTargetEnum;
use Mush\Modifier\Service\ModifierRequirementServiceInterface;
use Mush\Modifier\Service\ModifierService;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ModifierServiceTest extends TestCase
{
    /** @var EntityManagerInterface|Mockery\Mock */
    private EntityManagerInterface $entityManager;
    /** @var RandomServiceInterface|Mockery\Mock */
    private RandomServiceInterface $randomService;
    /** @var ModifierRequirementServiceInterface|Mockery\Mock */
    private ModifierRequirementServiceInterface $activationRequirementService;
    /** @var EventDispatcherInterface|Mockery\Mock */
    private EventDispatcherInterface $eventDispatcher;

    private ModifierService $service;

    /**
     * @before
     */
    public function before()
    {
        $this->entityManager = \Mockery::mock(EntityManagerInterface::class);
        $this->eventDispatcher = \Mockery::mock(EventDispatcherInterface::class);
        $this->activationRequirementService = \Mockery::mock(ModifierRequirementServiceInterface::class);
        $this->randomService = \Mockery::mock(RandomServiceInterface::class);

        $this->service = new ModifierService(
            $this->entityManager,
            $this->eventDispatcher,
            $this->activationRequirementService,
            $this->randomService,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testPersist()
    {
        $playerModifier = new GameModifier(new Player(), new ModifierConfig());

        $this->entityManager->shouldReceive('persist')->with($playerModifier)->once();
        $this->entityManager->shouldReceive('flush')->once();

        $this->service->persist($playerModifier);
    }

    public function testDelete()
    {
        $playerModifier = new GameModifier(new Player(), new ModifierConfig());

        $this->entityManager->shouldReceive('remove')->with($playerModifier)->once();
        $this->entityManager->shouldReceive('flush')->once();

        $this->service->delete($playerModifier);
    }

    public function testCreateModifier()
    {
        $daedalus = new Daedalus();

        // create a daedalus GameModifier
        $modifierConfig = new ModifierConfig();
        $modifierConfig->setModifierHolderClass(ModifierHolderClassEnum::DAEDALUS);

        $this->entityManager
            ->shouldReceive('persist')
            ->withArgs(fn (GameModifier $modifier) => $modifier->getModifierHolder() instanceof Daedalus)
            ->once();
        $this->entityManager->shouldReceive('flush')->once();

        $this->service->createModifier($modifierConfig, $daedalus);

        // create a place GameModifier
        $room = new Place();
        $modifierConfig = new ModifierConfig();
        $modifierConfig->setModifierHolderClass(ModifierHolderClassEnum::PLACE);

        $this->entityManager
            ->shouldReceive('persist')
            ->withArgs(fn (GameModifier $modifier) => $modifier->getModifierHolder() instanceof Place)
            ->once()
        ;
        $this->entityManager->shouldReceive('flush')->once();

        $this->service->createModifier($modifierConfig, $room);

        // create a player GameModifier
        $player = new Player();
        $modifierConfig = new ModifierConfig();
        $modifierConfig->setModifierHolderClass(ModifierHolderClassEnum::TARGET_PLAYER);

        $this->entityManager
            ->shouldReceive('persist')
            ->withArgs(fn (GameModifier $modifier) => $modifier->getModifierHolder() instanceof Player)
            ->once()
        ;
        $this->entityManager->shouldReceive('flush')->once();

        $this->service->createModifier($modifierConfig, $player);

        // create a player GameModifier with charge
        $player = new Player();
        $charge = new ChargeStatus($player, new ChargeStatusConfig());

        $modifierConfig = new ModifierConfig();
        $modifierConfig->setModifierHolderClass(ModifierHolderClassEnum::TARGET_PLAYER);

        $this->entityManager
            ->shouldReceive('persist')
            ->withArgs(fn (GameModifier $modifier) => (
                $modifier->getModifierHolder() === $player &&
                $modifier->getModifierConfig() === $modifierConfig &&
                $modifier->getCharge() === $charge
            ))
            ->once()
        ;
        $this->entityManager->shouldReceive('flush')->once();

        $this->service->createModifier($modifierConfig, $player, $charge);

        // create an equipment GameModifier
        $equipment = new GameEquipment(new Place());
        $modifierConfig = new ModifierConfig();
        $modifierConfig->setModifierHolderClass(ModifierHolderClassEnum::EQUIPMENT);

        $this->entityManager
            ->shouldReceive('persist')
            ->withArgs(fn (GameModifier $modifier) => $modifier->getModifierHolder() instanceof GameEquipment)
            ->once();
        $this->entityManager->shouldReceive('flush')->once();

        $this->service->createModifier($modifierConfig, $equipment);
    }

    public function testGetActionModifiedActionPointCost()
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
            ->setActionCost(1)
        ;

        $this->activationRequirementService
            ->shouldReceive('getActiveModifiers')
            ->withArgs(fn (ModifierCollection $modifiers) => $modifiers->count() === 0)
            ->andReturn(new ModifierCollection([]))
            ->once()
        ;
        // get action point modified without modifiers
        $modifiedCost = $this->service->getActionModifiedValue($action, $player, PlayerVariableEnum::ACTION_POINT, null);

        $this->assertEquals(1, $modifiedCost);

        // get action point modified with irrelevant modifiers
        $modifierConfig1 = new ModifierConfig();
        $modifierConfig1
            ->setModifierHolderClass(ModifierHolderClassEnum::DAEDALUS)
            ->setTargetEvent('action')
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(1)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;
        $modifier1 = new GameModifier($daedalus, $modifierConfig1);

        $modifierConfig2 = new ModifierConfig();
        $modifierConfig2
            ->setModifierHolderClass(ModifierHolderClassEnum::DAEDALUS)
            ->setTargetEvent('notThisAction')
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(1)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;
        $modifier2 = new GameModifier($daedalus, $modifierConfig2);

        $this->activationRequirementService
            ->shouldReceive('getActiveModifiers')
            ->withArgs(fn (ModifierCollection $modifiers) => $modifiers->count() === 1)
            ->andReturn(new ModifierCollection([]))
            ->once()
        ;
        $modifiedCost = $this->service->getActionModifiedValue($action, $player, PlayerVariableEnum::ACTION_POINT, null);

        $this->assertEquals(1, $modifiedCost);

        // now add a relevant modifier
        $modifierConfig3 = new ModifierConfig();
        $modifierConfig3
            ->setModifierHolderClass(ModifierHolderClassEnum::DAEDALUS)
            ->setTargetEvent('action')
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(1)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;
        $modifier3 = new GameModifier($daedalus, $modifierConfig3);

        $this->activationRequirementService
            ->shouldReceive('getActiveModifiers')
            ->withArgs(fn (ModifierCollection $modifiers) => $modifiers->count() === 2)
            ->andReturn(new ModifierCollection([$modifier3]))
            ->once()
        ;
        $modifiedCost = $this->service->getActionModifiedValue($action, $player, PlayerVariableEnum::ACTION_POINT, null);

        $this->assertEquals(2, $modifiedCost);

        // add another relevant modifier
        $modifierConfig4 = new ModifierConfig();
        $modifierConfig4
            ->setModifierHolderClass(ModifierHolderClassEnum::DAEDALUS)
            ->setTargetEvent('type1')
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(2)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;
        $modifier4 = new GameModifier($daedalus, $modifierConfig4);

        $this->activationRequirementService
            ->shouldReceive('getActiveModifiers')
            ->withArgs(fn (ModifierCollection $modifiers) => $modifiers->count() === 3)
            ->andReturn(new ModifierCollection([$modifier3, $modifier4]))
            ->once()
        ;
        $modifiedCost = $this->service->getActionModifiedValue($action, $player, PlayerVariableEnum::ACTION_POINT, null);

        $this->assertEquals(4, $modifiedCost);
    }

    public function testGetActionModifiedFromDifferentSources()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);
        $player = new Player();
        $player->setDaedalus($daedalus)->setPlace($room);
        $gameEquipment = new GameEquipment($room);

        $action = new Action();
        $action
            ->setActionName('action')
            ->setTypes(['type1', 'type2'])
            ->setActionCost(1)
        ;

        // Daedalus GameModifier
        $modifierConfig1 = new ModifierConfig();
        $modifierConfig1
            ->setModifierHolderClass(ModifierHolderClassEnum::DAEDALUS)
            ->setTargetEvent('action')
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(2)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;
        $modifier1 = new GameModifier($daedalus, $modifierConfig1);

        // Place GameModifier
        $modifierConfig2 = new ModifierConfig();
        $modifierConfig2
            ->setModifierHolderClass(ModifierHolderClassEnum::PLACE)
            ->setTargetEvent('action')
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(3)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;
        $modifier2 = new GameModifier($room, $modifierConfig2);

        // Player GameModifier
        $modifierConfig3 = new ModifierConfig();
        $modifierConfig3
            ->setModifierHolderClass(ModifierHolderClassEnum::PLAYER)
            ->setTargetEvent('action')
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(5)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;
        $modifier3 = new GameModifier($player, $modifierConfig3);

        // Equipment GameModifier
        $modifierConfig4 = new ModifierConfig();
        $modifierConfig4
            ->setModifierHolderClass(ModifierHolderClassEnum::EQUIPMENT)
            ->setTargetEvent('action')
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(7)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;
        $modifier4 = new GameModifier($gameEquipment, $modifierConfig4);

        $this->activationRequirementService
            ->shouldReceive('getActiveModifiers')
            ->withArgs(fn (ModifierCollection $modifiers) => $modifiers->count() === 4)
            ->andReturn(new ModifierCollection([$modifier1, $modifier2, $modifier3, $modifier4]))
            ->once()
        ;
        $modifiedCost = $this->service->getActionModifiedValue($action, $player, PlayerVariableEnum::ACTION_POINT, $gameEquipment);

        $this->assertEquals(18, $modifiedCost);
    }

    public function testGetActionModifiedForDifferentTargets()
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
        ;

        // Movement Point
        $modifierConfig1 = new ModifierConfig();
        $modifierConfig1
            ->setModifierHolderClass(ModifierHolderClassEnum::DAEDALUS)
            ->setTargetEvent('action')
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(-1)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;
        $modifier1 = new GameModifier($daedalus, $modifierConfig1);

        $this->activationRequirementService
            ->shouldReceive('getActiveModifiers')
            ->withArgs(fn (ModifierCollection $modifiers) => $modifiers->count() === 1)
            ->andReturn(new ModifierCollection([$modifier1]))
            ->once()
        ;
        $modifiedCost = $this->service->getActionModifiedValue($action, $player, PlayerVariableEnum::MOVEMENT_POINT, null);

        $this->assertEquals(0, $modifiedCost);

        // Moral point
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);
        $player = new Player();
        $player->setDaedalus($daedalus)->setPlace($room);

        $action->setMoralCost(2)->setMovementCost(0);

        $modifierConfig1 = new ModifierConfig();
        $modifierConfig1
            ->setModifierHolderClass(ModifierHolderClassEnum::DAEDALUS)
            ->setTargetEvent('action')
            ->setTargetVariable(PlayerVariableEnum::MORAL_POINT)
            ->setDelta(-1)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;
        $modifier1 = new GameModifier($daedalus, $modifierConfig1);

        $this->activationRequirementService
            ->shouldReceive('getActiveModifiers')
            ->withArgs(fn (ModifierCollection $modifiers) => $modifiers->count() === 1)
            ->andReturn(new ModifierCollection([$modifier1]))
            ->once()
        ;
        $modifiedCost = $this->service->getActionModifiedValue($action, $player, PlayerVariableEnum::MORAL_POINT, null);

        $this->assertEquals(1, $modifiedCost);

        // Percentage
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);
        $player = new Player();
        $player->setDaedalus($daedalus)->setPlace($room);

        $action = new Action();
        $action
            ->setActionName('action')
            ->setTypes(['type1', 'type2'])
            ->setMoralCost(2)
            ->setSuccessRate(50)
        ;

        $modifierConfig1 = new ModifierConfig();
        $modifierConfig1
            ->setModifierHolderClass(ModifierHolderClassEnum::DAEDALUS)
            ->setTargetEvent('action')
            ->setTargetVariable(ModifierTargetEnum::PERCENTAGE)
            ->setDelta(-10)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;
        $modifier1 = new GameModifier($daedalus, $modifierConfig1);

        $this->activationRequirementService
            ->shouldReceive('getActiveModifiers')
            ->withArgs(fn (ModifierCollection $modifiers) => $modifiers->count() === 1)
            ->andReturn(new ModifierCollection([$modifier1]))
            ->once()
        ;
        $modifiedCost = $this->service->getActionModifiedValue($action, $player, ModifierTargetEnum::PERCENTAGE, null, 0);

        $this->assertEquals(40, $modifiedCost);
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
        $modifierConfig1 = new ModifierConfig();
        $modifierConfig1
            ->setModifierHolderClass(ModifierHolderClassEnum::DAEDALUS)
            ->setTargetEvent('action')
            ->setTargetVariable(ModifierTargetEnum::PERCENTAGE)
            ->setDelta(1.5)
            ->setMode(ModifierModeEnum::MULTIPLICATIVE)
        ;
        $modifier1 = new GameModifier($daedalus, $modifierConfig1);

        $this->activationRequirementService
            ->shouldReceive('getActiveModifiers')
            ->withArgs(fn (ModifierCollection $modifiers) => $modifiers->count() === 1)
            ->andReturn(new ModifierCollection([$modifier1]))
            ->once()
        ;
        $modifiedCost = $this->service->getActionModifiedValue($action, $player, ModifierTargetEnum::PERCENTAGE, null, 0);

        $this->assertEquals(75, $modifiedCost);

        // multiplicative and additive
        $modifierConfig2 = new ModifierConfig();
        $modifierConfig2
            ->setModifierHolderClass(ModifierHolderClassEnum::DAEDALUS)
            ->setTargetEvent('action')
            ->setTargetVariable(ModifierTargetEnum::PERCENTAGE)
            ->setDelta(10)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;
        $modifier2 = new GameModifier($daedalus, $modifierConfig2);

        $this->activationRequirementService
            ->shouldReceive('getActiveModifiers')
            ->withArgs(fn (ModifierCollection $modifiers) => $modifiers->count() === 2)
            ->andReturn(new ModifierCollection([$modifier1, $modifier2]))
            ->once()
        ;
        $modifiedCost = $this->service->getActionModifiedValue($action, $player, ModifierTargetEnum::PERCENTAGE, null, 0);

        $this->assertEquals(85, $modifiedCost);

        // add attempt
        $this->activationRequirementService
            ->shouldReceive('getActiveModifiers')
            ->withArgs(fn (ModifierCollection $modifiers) => $modifiers->count() === 2)
            ->andReturn(new ModifierCollection([$modifier1, $modifier2]))
            ->once()
        ;
        $modifiedCost = $this->service->getActionModifiedValue($action, $player, ModifierTargetEnum::PERCENTAGE, null, 1);
        $this->assertEquals(intval(50 * 1.25 ** 1 * 1.5 + 10), $modifiedCost);

        $this->activationRequirementService
            ->shouldReceive('getActiveModifiers')
            ->withArgs(fn (ModifierCollection $modifiers) => $modifiers->count() === 2)
            ->andReturn(new ModifierCollection([$modifier1, $modifier2]))
            ->once()
        ;
        $modifiedCost = $this->service->getActionModifiedValue($action, $player, ModifierTargetEnum::PERCENTAGE, null, 3);
        $this->assertEquals(intval(50 * 1.25 ** 3 * 1.5 + 10), $modifiedCost);
    }

//    public function testModifyNullValue()
//    {
//        $daedalus = new Daedalus();
//        $room = new Place();
//        $room->setDaedalus($daedalus);
//        $player = new Player();
//        $player->setDaedalus($daedalus)->setPlace($room);
//
//        $actionCost = new ActionVariables(0,0,0, 0, 0);
//
//        $action = new Action();
//        $action->setActionName('action')->setTypes(['type1', 'type2'])->setActionVariables($actionCost);
//
//        $modifierConfig1 = new ModifierConfig();
//        $modifierConfig1
//            ->setModifierHolderClass(ModifierHolderClassEnum::DAEDALUS)
//            ->setTargetEvent('action')
//            ->setTarget(PlayerVariableEnum::ACTION_POINT)
//            ->setDelta(5)
//            ->setMode(ModifierModeEnum::ADDITIVE)
//        ;
//        $modifier1 = new GameModifier($daedalus, $modifierConfig1);
//
//        $this->conditionService
//            ->shouldReceive('getActiveModifiers')
//            ->withArgs(fn (ModifierCollection $modifiers) => $modifiers->count() === 1)
//            ->andReturn(new ModifierCollection([$modifier1]))
//            ->once()
//        ;
//        $modifiedCost = $this->service->getActionModifiedValue($action, $player, PlayerVariableEnum::ACTION_POINT, null, null);
//
//        $this->assertEquals(0, $modifiedCost);
//    }

    public function testConsumeModifierCharge()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);
        $player = new Player();
        $player->setDaedalus($daedalus)->setPlace($room);

        $status = new ChargeStatus($player, new ChargeStatusConfig());
        $status->setCharge(5);

        $action = new Action();
        $action
            ->setActionName('action')
            ->setTypes(['type1', 'type2'])
            ->setActionCost(1)
        ;

        $modifierConfig1 = new ModifierConfig();
        $modifierConfig1
            ->setModifierHolderClass(ModifierHolderClassEnum::PLAYER)
            ->setTargetEvent('action')
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;
        $modifier1 = new GameModifier($player, $modifierConfig1);
        $modifier1->setCharge($status);

        $this->activationRequirementService
            ->shouldReceive('getActiveModifiers')
            ->withArgs(fn (ModifierCollection $modifiers) => $modifiers->count() === 1)
            ->andReturn(new ModifierCollection([$modifier1]))
            ->once()
        ;
        $this->eventDispatcher->shouldReceive('dispatch')->once();

        $this->service->applyActionModifiers($action, $player, null);
    }

    public function testGetEventModifiedValue()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);
        $player = new Player();
        $player->setDaedalus($daedalus)->setPlace($room);

        $this->activationRequirementService->shouldReceive('getActiveModifiers')->andReturn(new ModifierCollection())->once();
        $this->eventDispatcher->shouldReceive('dispatch')->once();

        $modifiedValue = $this->service->getEventModifiedValue(
            $player,
            [ModifierScopeEnum::MAX_POINT],
            PlayerVariableEnum::MOVEMENT_POINT,
            12,
            ModifierScopeEnum::MAX_POINT,
            new \DateTime()
        );
        $this->assertEquals(12, $modifiedValue);

        $modifierConfig1 = new ModifierConfig();
        $modifierConfig1
            ->setModifierHolderClass(ModifierHolderClassEnum::DAEDALUS)
            ->setTargetEvent(ModifierScopeEnum::MAX_POINT)
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(-6)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;
        $modifier1 = new GameModifier($daedalus, $modifierConfig1);

        $this->activationRequirementService->shouldReceive('getActiveModifiers')->andReturn(new ModifierCollection([$modifier1]))->once();
        $this->eventDispatcher->shouldReceive('dispatch')->once();

        $modifiedValue = $this->service->getEventModifiedValue(
            $player,
            [ModifierScopeEnum::MAX_POINT],
            PlayerVariableEnum::MOVEMENT_POINT,
            12,
            ModifierScopeEnum::MAX_POINT,
            new \DateTime()
        );
        $this->assertEquals(6, $modifiedValue);

        // add a modifier with a charge
        $status = new ChargeStatus($player, new ChargeStatusConfig());
        $status->setCharge(5);

        $modifierConfig2 = new ModifierConfig();
        $modifierConfig2
            ->setModifierHolderClass(ModifierHolderClassEnum::PLAYER)
            ->setTargetEvent(ModifierScopeEnum::MAX_POINT)
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(2)
            ->setMode(ModifierModeEnum::MULTIPLICATIVE)
        ;
        $modifier2 = new GameModifier($player, $modifierConfig2);
        $modifier2->setCharge($status);

        $this->eventDispatcher->shouldReceive('dispatch')->once();
        $this->activationRequirementService->shouldReceive('getActiveModifiers')->andReturn(new ModifierCollection([$modifier1, $modifier2]))->once();

        $modifiedValue = $this->service->getEventModifiedValue(
            $player,
            [ModifierScopeEnum::MAX_POINT],
            PlayerVariableEnum::MOVEMENT_POINT,
            12,
            ModifierScopeEnum::MAX_POINT,
            new \DateTime()
        );
        $this->assertEquals(18, $modifiedValue);
    }

    public function testGetEventModifiedValueWithChangeInSign()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);
        $player = new Player();
        $player->setDaedalus($daedalus)->setPlace($room);

        $modifierConfig1 = new ModifierConfig();
        $modifierConfig1
            ->setModifierHolderClass(ModifierHolderClassEnum::DAEDALUS)
            ->setTargetEvent(ModifierScopeEnum::MAX_POINT)
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(-6)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;
        $modifier1 = new GameModifier($daedalus, $modifierConfig1);

        $this->activationRequirementService->shouldReceive('getActiveModifiers')->andReturn(new ModifierCollection([$modifier1]))->once();
        $this->eventDispatcher->shouldReceive('dispatch')->once();

        $modifiedValue = $this->service->getEventModifiedValue(
            $player,
            [ModifierScopeEnum::MAX_POINT],
            PlayerVariableEnum::MOVEMENT_POINT,
            4,
            ModifierScopeEnum::MAX_POINT,
            new \DateTime()
        );
        $this->assertEquals(0, $modifiedValue);
    }
}
