<?php

namespace Mush\Test\Modifier\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\Modifier;
use Mush\Modifier\Entity\ModifierConfig;
use Mush\Modifier\Enum\ModifierModeEnum;
use Mush\Modifier\Enum\ModifierReachEnum;
use Mush\Modifier\Enum\ModifierScopeEnum;
use Mush\Modifier\Enum\ModifierTargetEnum;
use Mush\Modifier\Service\ModifierConditionServiceInterface;
use Mush\Modifier\Service\ModifierService;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Service\StatusServiceInterface;
use PHPUnit\Framework\TestCase;

class ModifierServiceTest extends TestCase
{
    /** @var EntityManagerInterface|Mockery\Mock */
    private EntityManagerInterface $entityManager;
    /** @var StatusServiceInterface|Mockery\Mock */
    private StatusServiceInterface $statusService;
    /** @var ModifierConditionServiceInterface|Mockery\Mock */
    private ModifierConditionServiceInterface $conditionService;

    private ModifierService $service;

    /**
     * @before
     */
    public function before()
    {
        $this->entityManager = Mockery::mock(EntityManagerInterface::class);
        $this->statusService = Mockery::mock(StatusServiceInterface::class);
        $this->conditionService = Mockery::mock(ModifierConditionServiceInterface::class);

        $this->service = new ModifierService(
            $this->entityManager,
            $this->statusService,
            $this->conditionService,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testPersist()
    {
        $playerModifier = new Modifier(new Player(), new ModifierConfig());

        $this->entityManager->shouldReceive('persist')->with($playerModifier)->once();
        $this->entityManager->shouldReceive('flush')->once();

        $this->service->persist($playerModifier);
    }

    public function testDelete()
    {
        $playerModifier = new Modifier(new Player(), new ModifierConfig());

        $this->entityManager->shouldReceive('remove')->with($playerModifier)->once();
        $this->entityManager->shouldReceive('flush')->once();

        $this->service->delete($playerModifier);
    }

    public function testCreateModifier()
    {
        $daedalus = new Daedalus();

        // create a daedalus Modifier
        $modifierConfig = new ModifierConfig();
        $modifierConfig->setReach(ModifierReachEnum::DAEDALUS);

        $this->entityManager
            ->shouldReceive('persist')
            ->withArgs(fn (Modifier $modifier) => $modifier->getModifierHolder() instanceof Daedalus)
            ->once();
        $this->entityManager->shouldReceive('flush')->once();

        $this->service->createModifier($modifierConfig, $daedalus, null, null, null, null);

        // create a place Modifier
        $room = new Place();
        $modifierConfig = new ModifierConfig();
        $modifierConfig->setReach(ModifierReachEnum::PLACE);

        $this->entityManager
            ->shouldReceive('persist')
            ->withArgs(fn (Modifier $modifier) => $modifier->getModifierHolder() instanceof Place)
            ->once()
        ;
        $this->entityManager->shouldReceive('flush')->once();

        $this->service->createModifier($modifierConfig, $daedalus, $room, null, null, null);

        // create a player Modifier
        $player = new Player();
        $modifierConfig = new ModifierConfig();
        $modifierConfig->setReach(ModifierReachEnum::TARGET_PLAYER);

        $this->entityManager
            ->shouldReceive('persist')
            ->withArgs(fn (Modifier $modifier) => $modifier->getModifierHolder() instanceof Player)
            ->once()
        ;
        $this->entityManager->shouldReceive('flush')->once();

        $this->service->createModifier($modifierConfig, $daedalus, null, $player, null, null);

        // create a player Modifier with charge
        $player = new Player();
        $charge = new ChargeStatus($player, new ChargeStatusConfig());

        $modifierConfig = new ModifierConfig();
        $modifierConfig->setReach(ModifierReachEnum::TARGET_PLAYER);

        $this->entityManager
            ->shouldReceive('persist')
            ->withArgs(fn (Modifier $modifier) => (
                $modifier->getModifierHolder() === $player &&
                $modifier->getModifierConfig() === $modifierConfig &&
                $modifier->getCharge() === $charge
            ))
            ->once()
        ;
        $this->entityManager->shouldReceive('flush')->once();

        $this->service->createModifier($modifierConfig, $daedalus, null, $player, null, $charge);

        // create an equipment Modifier
        $equipment = new GameEquipment();
        $modifierConfig = new ModifierConfig();
        $modifierConfig->setReach(ModifierReachEnum::EQUIPMENT);

        $this->entityManager
            ->shouldReceive('persist')
            ->withArgs(fn (Modifier $modifier) => $modifier->getModifierHolder() instanceof GameEquipment)
            ->once();
        $this->entityManager->shouldReceive('flush')->once();

        $this->service->createModifier($modifierConfig, $daedalus, null, null, $equipment, null);
    }

    public function testGetActionModifiedActionPointCost()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);
        $player = new Player();
        $player->setDaedalus($daedalus)->setPlace($room);

        $actionCost = new ActionCost();
        $actionCost
            ->setActionPointCost(1)
            ->setMovementPointCost(null)
            ->setMoralPointCost(null)
        ;
        $action = new Action();
        $action->setName('action')->setTypes(['type1', 'type2'])->setActionCost($actionCost);

        $this->conditionService
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
            ->setReach(ModifierReachEnum::DAEDALUS)
            ->setScope('action')
            ->setTarget(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(1)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;
        $modifier1 = new Modifier($daedalus, $modifierConfig1);

        $modifierConfig2 = new ModifierConfig();
        $modifierConfig2
            ->setReach(ModifierReachEnum::DAEDALUS)
            ->setScope('notThisAction')
            ->setTarget(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(1)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;
        $modifier2 = new Modifier($daedalus, $modifierConfig2);

        $this->conditionService
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
            ->setReach(ModifierReachEnum::DAEDALUS)
            ->setScope('action')
            ->setTarget(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(1)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;
        $modifier3 = new Modifier($daedalus, $modifierConfig3);

        $this->conditionService
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
            ->setReach(ModifierReachEnum::DAEDALUS)
            ->setScope('type1')
            ->setTarget(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(2)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;
        $modifier4 = new Modifier($daedalus, $modifierConfig4);

        $this->conditionService
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
        $gameEquipment = new GameEquipment();

        $actionCost = new ActionCost();
        $actionCost
            ->setActionPointCost(1)
            ->setMovementPointCost(null)
            ->setMoralPointCost(null)
        ;
        $action = new Action();
        $action->setName('action')->setTypes(['type1', 'type2'])->setActionCost($actionCost);

        //Daedalus Modifier
        $modifierConfig1 = new ModifierConfig();
        $modifierConfig1
            ->setReach(ModifierReachEnum::DAEDALUS)
            ->setScope('action')
            ->setTarget(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(2)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;
        $modifier1 = new Modifier($daedalus, $modifierConfig1);

        //Place Modifier
        $modifierConfig2 = new ModifierConfig();
        $modifierConfig2
            ->setReach(ModifierReachEnum::PLACE)
            ->setScope('action')
            ->setTarget(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(3)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;
        $modifier2 = new Modifier($room, $modifierConfig2);

        //Player Modifier
        $modifierConfig3 = new ModifierConfig();
        $modifierConfig3
            ->setReach(ModifierReachEnum::PLAYER)
            ->setScope('action')
            ->setTarget(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(5)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;
        $modifier3 = new Modifier($player, $modifierConfig3);

        //Equipment Modifier
        $modifierConfig4 = new ModifierConfig();
        $modifierConfig4
            ->setReach(ModifierReachEnum::EQUIPMENT)
            ->setScope('action')
            ->setTarget(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(7)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;
        $modifier4 = new Modifier($gameEquipment, $modifierConfig4);

        $this->conditionService
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

        $actionCost = new ActionCost();
        $actionCost
            ->setActionPointCost(null)
            ->setMovementPointCost(1)
            ->setMoralPointCost(null)
        ;
        $action = new Action();
        $action->setName('action')->setTypes(['type1', 'type2'])->setActionCost($actionCost);

        //Movement Point
        $modifierConfig1 = new ModifierConfig();
        $modifierConfig1
            ->setReach(ModifierReachEnum::DAEDALUS)
            ->setScope('action')
            ->setTarget(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(-1)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;
        $modifier1 = new Modifier($daedalus, $modifierConfig1);

        $this->conditionService
            ->shouldReceive('getActiveModifiers')
            ->withArgs(fn (ModifierCollection $modifiers) => $modifiers->count() === 1)
            ->andReturn(new ModifierCollection([$modifier1]))
            ->once()
        ;
        $modifiedCost = $this->service->getActionModifiedValue($action, $player, PlayerVariableEnum::MOVEMENT_POINT, null);

        $this->assertEquals(0, $modifiedCost);

        //Moral point
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);
        $player = new Player();
        $player->setDaedalus($daedalus)->setPlace($room);

        $actionCost
            ->setActionPointCost(null)
            ->setMovementPointCost(null)
            ->setMoralPointCost(2)
        ;
        $action->setActionCost($actionCost);

        $modifierConfig1 = new ModifierConfig();
        $modifierConfig1
            ->setReach(ModifierReachEnum::DAEDALUS)
            ->setScope('action')
            ->setTarget(PlayerVariableEnum::MORAL_POINT)
            ->setDelta(-1)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;
        $modifier1 = new Modifier($daedalus, $modifierConfig1);

        $this->conditionService
            ->shouldReceive('getActiveModifiers')
            ->withArgs(fn (ModifierCollection $modifiers) => $modifiers->count() === 1)
            ->andReturn(new ModifierCollection([$modifier1]))
            ->once()
        ;
        $modifiedCost = $this->service->getActionModifiedValue($action, $player, PlayerVariableEnum::MORAL_POINT, null);

        $this->assertEquals(1, $modifiedCost);

        //Percentage
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);
        $player = new Player();
        $player->setDaedalus($daedalus)->setPlace($room);

        $actionCost
            ->setActionPointCost(null)
            ->setMovementPointCost(null)
            ->setMoralPointCost(2)
        ;

        $action = new Action();
        $action->setName('action')->setTypes(['type1', 'type2'])->setActionCost($actionCost)->setSuccessRate(50);

        $modifierConfig1 = new ModifierConfig();
        $modifierConfig1
            ->setReach(ModifierReachEnum::DAEDALUS)
            ->setScope('action')
            ->setTarget(ModifierTargetEnum::PERCENTAGE)
            ->setDelta(-10)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;
        $modifier1 = new Modifier($daedalus, $modifierConfig1);

        $this->conditionService
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

        $actionCost = new ActionCost();
        $actionCost
            ->setActionPointCost(null)
            ->setMovementPointCost(1)
            ->setMoralPointCost(null)
        ;

        $action = new Action();
        $action->setName('action')->setTypes(['type1', 'type2'])->setActionCost($actionCost)->setSuccessRate(50);

        //multiplicative
        $modifierConfig1 = new ModifierConfig();
        $modifierConfig1
            ->setReach(ModifierReachEnum::DAEDALUS)
            ->setScope('action')
            ->setTarget(ModifierTargetEnum::PERCENTAGE)
            ->setDelta(1.5)
            ->setMode(ModifierModeEnum::MULTIPLICATIVE)
        ;
        $modifier1 = new Modifier($daedalus, $modifierConfig1);

        $this->conditionService
            ->shouldReceive('getActiveModifiers')
            ->withArgs(fn (ModifierCollection $modifiers) => $modifiers->count() === 1)
            ->andReturn(new ModifierCollection([$modifier1]))
            ->once()
        ;
        $modifiedCost = $this->service->getActionModifiedValue($action, $player, ModifierTargetEnum::PERCENTAGE, null, 0);

        $this->assertEquals(75, $modifiedCost);

        //multiplicative and additive
        $modifierConfig2 = new ModifierConfig();
        $modifierConfig2
            ->setReach(ModifierReachEnum::DAEDALUS)
            ->setScope('action')
            ->setTarget(ModifierTargetEnum::PERCENTAGE)
            ->setDelta(10)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;
        $modifier2 = new Modifier($daedalus, $modifierConfig2);

        $this->conditionService
            ->shouldReceive('getActiveModifiers')
            ->withArgs(fn (ModifierCollection $modifiers) => $modifiers->count() === 2)
            ->andReturn(new ModifierCollection([$modifier1, $modifier2]))
            ->once()
        ;
        $modifiedCost = $this->service->getActionModifiedValue($action, $player, ModifierTargetEnum::PERCENTAGE, null, 0);

        $this->assertEquals(85, $modifiedCost);

        // add attempt
        $this->conditionService
            ->shouldReceive('getActiveModifiers')
            ->withArgs(fn (ModifierCollection $modifiers) => $modifiers->count() === 2)
            ->andReturn(new ModifierCollection([$modifier1, $modifier2]))
            ->once()
        ;
        $modifiedCost = $this->service->getActionModifiedValue($action, $player, ModifierTargetEnum::PERCENTAGE, null, 1);
        $this->assertEquals(intval(50 * 1.25 ** 1 * 1.5 + 10), $modifiedCost);

        $this->conditionService
            ->shouldReceive('getActiveModifiers')
            ->withArgs(fn (ModifierCollection $modifiers) => $modifiers->count() === 2)
            ->andReturn(new ModifierCollection([$modifier1, $modifier2]))
            ->once()
        ;
        $modifiedCost = $this->service->getActionModifiedValue($action, $player, ModifierTargetEnum::PERCENTAGE, null, 3);
        $this->assertEquals(intval(50 * 1.25 ** 3 * 1.5 + 10), $modifiedCost);
    }

    public function testModifyNullValue()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);
        $player = new Player();
        $player->setDaedalus($daedalus)->setPlace($room);

        $actionCost = new ActionCost();
        $actionCost
            ->setActionPointCost(null)
            ->setMovementPointCost(null)
            ->setMoralPointCost(null)
        ;

        $action = new Action();
        $action->setName('action')->setTypes(['type1', 'type2'])->setActionCost($actionCost);

        $modifierConfig1 = new ModifierConfig();
        $modifierConfig1
            ->setReach(ModifierReachEnum::DAEDALUS)
            ->setScope('action')
            ->setTarget(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(5)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;
        $modifier1 = new Modifier($daedalus, $modifierConfig1);

        $this->conditionService
            ->shouldReceive('getActiveModifiers')
            ->withArgs(fn (ModifierCollection $modifiers) => $modifiers->count() === 1)
            ->andReturn(new ModifierCollection([$modifier1]))
            ->once()
        ;
        $modifiedCost = $this->service->getActionModifiedValue($action, $player, PlayerVariableEnum::ACTION_POINT, null, null);

        $this->assertEquals(0, $modifiedCost);
    }

    public function testConsumeModifierCharge()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);
        $player = new Player();
        $player->setDaedalus($daedalus)->setPlace($room);

        $status = new ChargeStatus($player, new ChargeStatusConfig());
        $status->setCharge(5);

        $actionCost = new ActionCost();
        $actionCost
            ->setActionPointCost(1)
            ->setMovementPointCost(null)
            ->setMoralPointCost(null)
        ;

        $action = new Action();
        $action->setName('action')->setTypes(['type1', 'type2'])->setActionCost($actionCost);

        $modifierConfig1 = new ModifierConfig();
        $modifierConfig1
            ->setReach(ModifierReachEnum::PLAYER)
            ->setScope('action')
            ->setTarget(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;
        $modifier1 = new Modifier($player, $modifierConfig1);
        $modifier1->setCharge($status);

        $this->conditionService
            ->shouldReceive('getActiveModifiers')
            ->withArgs(fn (ModifierCollection $modifiers) => $modifiers->count() === 1)
            ->andReturn(new ModifierCollection([$modifier1]))
            ->once()
        ;
        $this->statusService->shouldReceive('updateCharge')->with($status, -1)->once();

        $this->service->consumeActionCharges($action, $player, null);
    }

    public function testGetEventModifiedValue()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);
        $player = new Player();
        $player->setDaedalus($daedalus)->setPlace($room);

        $this->conditionService->shouldReceive('getActiveModifiers')->andReturn(new ModifierCollection())->once();

        $modifiedValue = $this->service->getEventModifiedValue(
            $player,
            [ModifierScopeEnum::MAX_POINT],
            PlayerVariableEnum::MOVEMENT_POINT,
            12,
            ModifierScopeEnum::MAX_POINT
        );
        $this->assertEquals(12, $modifiedValue);

        $modifierConfig1 = new ModifierConfig();
        $modifierConfig1
            ->setReach(ModifierReachEnum::DAEDALUS)
            ->setScope(ModifierScopeEnum::MAX_POINT)
            ->setTarget(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(-6)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;
        $modifier1 = new Modifier($daedalus, $modifierConfig1);

        $this->conditionService->shouldReceive('getActiveModifiers')->andReturn(new ModifierCollection([$modifier1]))->once();

        $modifiedValue = $this->service->getEventModifiedValue(
            $player,
            [ModifierScopeEnum::MAX_POINT],
            PlayerVariableEnum::MOVEMENT_POINT,
            12,
            ModifierScopeEnum::MAX_POINT
        );
        $this->assertEquals(6, $modifiedValue);

        //add a modifier with a charge
        $status = new ChargeStatus($player, new ChargeStatusConfig());
        $status->setCharge(5);

        $modifierConfig2 = new ModifierConfig();
        $modifierConfig2
            ->setReach(ModifierReachEnum::PLAYER)
            ->setScope(ModifierScopeEnum::MAX_POINT)
            ->setTarget(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(2)
            ->setMode(ModifierModeEnum::MULTIPLICATIVE)
        ;
        $modifier2 = new Modifier($player, $modifierConfig2);
        $modifier2->setCharge($status);

        $this->statusService->shouldReceive('updateCharge')->with($status, -1)->once();
        $this->conditionService->shouldReceive('getActiveModifiers')->andReturn(new ModifierCollection([$modifier1, $modifier2]))->once();

        $modifiedValue = $this->service->getEventModifiedValue(
            $player,
            [ModifierScopeEnum::MAX_POINT],
            PlayerVariableEnum::MOVEMENT_POINT,
            12,
            ModifierScopeEnum::MAX_POINT
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
            ->setReach(ModifierReachEnum::DAEDALUS)
            ->setScope(ModifierScopeEnum::MAX_POINT)
            ->setTarget(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(-6)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;
        $modifier1 = new Modifier($daedalus, $modifierConfig1);

        $this->conditionService->shouldReceive('getActiveModifiers')->andReturn(new ModifierCollection([$modifier1]))->once();

        $modifiedValue = $this->service->getEventModifiedValue(
            $player,
            [ModifierScopeEnum::MAX_POINT],
            PlayerVariableEnum::MOVEMENT_POINT,
            4,
            ModifierScopeEnum::MAX_POINT
        );
        $this->assertEquals(0, $modifiedValue);
    }
}
