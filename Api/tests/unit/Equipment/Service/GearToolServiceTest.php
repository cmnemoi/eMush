<?php

namespace Mush\Tests\unit\Equipment\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Equipment\Entity\Mechanics\Tool;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Service\GearToolService;
use Mush\Game\Service\EventServiceInterface;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Enum\ModifierTargetEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use PHPUnit\Framework\TestCase;

class GearToolServiceTest extends TestCase
{
    /** @var EventServiceInterface|Mockery\Mock */
    private EventServiceInterface $eventService;
    /** @var StatusServiceInterface|Mockery\Mock */
    private StatusServiceInterface $statusService;

    private GearToolService $service;

    /**
     * @before
     */
    public function before()
    {
        $this->eventService = \Mockery::mock(EventServiceInterface::class);
        $this->statusService = \Mockery::mock(StatusServiceInterface::class);

        $this->service = new GearToolService(
            $this->eventService,
            $this->statusService
        );
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testGetEquipmentOnReach()
    {
        $room = new Place();
        $player = new Player();

        $item = new ItemConfig();
        $item->setEquipmentName(ItemEnum::METAL_SCRAPS);

        $gameItem = new GameItem($room);
        $gameItem
            ->setName(ItemEnum::METAL_SCRAPS)
            ->setEquipment($item)
        ;

        $item2 = new ItemConfig();
        $item2->setEquipmentName(ItemEnum::PLASTIC_SCRAPS);

        $gameItem2 = new GameItem($player);
        $gameItem2
            ->setName(ItemEnum::PLASTIC_SCRAPS)
            ->setEquipment($item2)
        ;

        $room
            ->addPlayer($player)
        ;

        $items = $this->service->getEquipmentsOnReach($player, ReachEnum::SHELVE);
        $this->assertCount(2, $items);

        $items = $this->service->getEquipmentsOnReach($player, ReachEnum::INVENTORY);
        $this->assertCount(1, $items);
        $this->assertEquals($gameItem2, $items->first());
    }

    public function testGetEquipmentsOnReachByName()
    {
        $item = new ItemConfig();
        $item->setEquipmentName(ItemEnum::METAL_SCRAPS);

        $room = new Place();

        $player = new Player();

        $gameItem = new GameItem($room);
        $gameItem
            ->setName(ItemEnum::METAL_SCRAPS)
            ->setEquipment($item)
        ;

        $room
            ->addPlayer($player)
        ;

        $items = $this->service->getEquipmentsOnReachByName($player, ItemEnum::PLASTIC_SCRAPS, ReachEnum::SHELVE);

        $this->assertEmpty($items);

        $items = $this->service->getEquipmentsOnReachByName($player, ItemEnum::METAL_SCRAPS, ReachEnum::INVENTORY);

        $this->assertEmpty($items);

        $items = $this->service->getEquipmentsOnReachByName($player, ItemEnum::METAL_SCRAPS, ReachEnum::SHELVE);

        $this->assertNotEmpty($items);

        $hiddenConfig = new StatusConfig();
        $hiddenConfig->setStatusName(EquipmentStatusEnum::HIDDEN);
        $hidden = new Status($gameItem, $hiddenConfig);
        $hidden
            ->setTarget(new Player())
        ;

        $items = $this->service->getEquipmentsOnReachByName($player, ItemEnum::METAL_SCRAPS, ReachEnum::SHELVE_NOT_HIDDEN);
        $this->assertEmpty($items);

        $items = $this->service->getEquipmentsOnReachByName($player, ItemEnum::METAL_SCRAPS, ReachEnum::SHELVE);
        $this->assertNotEmpty($items);

        $gameItem2 = new GameItem($player);
        $gameItem2
            ->setName(ItemEnum::METAL_SCRAPS)
            ->setEquipment($item)
        ;

        $items = $this->service->getEquipmentsOnReachByName($player, ItemEnum::METAL_SCRAPS, ReachEnum::INVENTORY);
        $this->assertCount(1, $items);

        $items = $this->service->getEquipmentsOnReachByName($player, ItemEnum::METAL_SCRAPS, ReachEnum::SHELVE);
        $this->assertCount(2, $items);
    }

    public function testGetActionsTool()
    {
        $room = new Place();
        $player = new Player();

        $action = new Action();
        $action
            ->setActionName(ActionEnum::SHOWER)
            ->setScope(ActionScopeEnum::CURRENT)
        ;

        $tool = new Tool();
        $tool->setActions(new ArrayCollection([$action]));

        $item = new ItemConfig();
        $item
            ->setEquipmentName(ItemEnum::METAL_SCRAPS)
            ->setMechanics(new ArrayCollection([$tool]))
        ;

        $gameItem = new GameItem($room);
        $gameItem
            ->setName(ItemEnum::METAL_SCRAPS)
            ->setEquipment($item)
        ;

        $room
            ->addPlayer($player)
        ;

        $actions = $this->service->getActionsTools($player, [ActionScopeEnum::ROOM]);
        $this->assertEmpty($actions);

        $action->setScope(ActionScopeEnum::ROOM);
        $actions = $this->service->getActionsTools($player, [ActionScopeEnum::ROOM]);
        $this->assertNotEmpty($actions);

        $action->setScope(ActionScopeEnum::INVENTORY);
        $actions = $this->service->getActionsTools($player, [ActionScopeEnum::ROOM]);
        $this->assertEmpty($actions);

        $actions = $this->service->getActionsTools($player, [ActionScopeEnum::ROOM, ActionScopeEnum::INVENTORY]);
        $this->assertNotEmpty($actions);

        $action->setTarget(GameItem::class);
        $actions = $this->service->getActionsTools($player, [ActionScopeEnum::ROOM, ActionScopeEnum::INVENTORY]);
        $this->assertEmpty($actions);

        $actions = $this->service->getActionsTools($player, [ActionScopeEnum::ROOM, ActionScopeEnum::INVENTORY], GameItem::class);
        $this->assertNotEmpty($actions);
    }

    public function testUsedTool()
    {
        $room = new Place();
        $player = new Player();

        $action = new Action();
        $action
            ->setActionName(ActionEnum::SHOWER)
            ->setScope(ActionScopeEnum::CURRENT)
        ;

        $tool = new Tool();
        $tool->setActions(new ArrayCollection([$action]));

        $item = new ItemConfig();
        $item
            ->setEquipmentName(ItemEnum::METAL_SCRAPS)
            ->setMechanics(new ArrayCollection([$tool]))
        ;

        $gameItem = new GameItem($room);
        $gameItem
            ->setName(ItemEnum::METAL_SCRAPS)
            ->setEquipment($item)
        ;

        $room
            ->addPlayer($player)
        ;

        $action2 = new Action();
        $action2
            ->setActionName(ActionEnum::REPAIR)
            ->setScope(ActionScopeEnum::ROOM)
        ;

        $tool2 = new Tool();
        $tool2->setActions(new ArrayCollection([$action2]));

        $item2 = new ItemConfig();
        $item2
            ->setEquipmentName(ItemEnum::PLASTIC_SCRAPS)
            ->setMechanics(new ArrayCollection([$tool2]))
        ;

        $gameItem2 = new GameItem($room);
        $gameItem2
            ->setName(ItemEnum::PLASTIC_SCRAPS)
            ->setEquipment($item2)
        ;

        $usedTool = $this->service->getUsedTool($player, ActionEnum::TAKE);
        $this->assertNull($usedTool);

        $usedTool = $this->service->getUsedTool($player, ActionEnum::REPAIR);
        $this->assertEquals($gameItem2, $usedTool);

        // Two tool with the same action but 1 with charges
        $chargeConfig = new ChargeStatusConfig();
        $chargeConfig->setStatusName(EquipmentStatusEnum::ELECTRIC_CHARGES)->setDischargeStrategies([ActionEnum::REPAIR]);
        $chargeStatus = new ChargeStatus($gameItem, $chargeConfig);
        $chargeStatus
            ->setCharge(3)
        ;

        $action->setActionName(ActionEnum::REPAIR);

        $usedTool = $this->service->getUsedTool($player, ActionEnum::REPAIR);
        $this->assertEquals($gameItem2, $usedTool);

        // Two tool with the same action but 1 is charged and the other have no charge left
        $chargeStatus2 = new ChargeStatus($gameItem2, $chargeConfig);
        $chargeStatus2
            ->setCharge(0)
        ;

        $usedTool = $this->service->getUsedTool($player, ActionEnum::REPAIR);
        $this->assertEquals($gameItem, $usedTool);
    }

    public function testApplyChargeCost()
    {
        $room = new Place();
        $player = new Player();

        $action = new Action();
        $action
            ->setActionName(ActionEnum::REPAIR)
            ->setScope(ActionScopeEnum::CURRENT)
        ;
        $tool = new Tool();
        $tool->setActions(new ArrayCollection([$action]));
        $toolConfig = new ItemConfig();
        $toolConfig
            ->setEquipmentName('tool')
            ->setMechanics(new ArrayCollection([$tool]))
        ;
        $gameTool = new GameItem($room);
        $gameTool
            ->setName('tool')
            ->setEquipment($toolConfig)
        ;

        $chargeConfig = new ChargeStatusConfig();
        $chargeConfig->setStatusName(EquipmentStatusEnum::ELECTRIC_CHARGES)->setDischargeStrategies([ActionEnum::REPAIR]);
        $chargeStatus1 = new ChargeStatus($gameTool, $chargeConfig);
        $chargeStatus1
            ->setCharge(1)
        ;

        $modifier1 = new VariableEventModifierConfig('unitTestVariableEventModifier');
        $modifier1
            ->setTargetVariable(ModifierTargetEnum::PERCENTAGE)
            ->setDelta(1.5)
            ->setTargetEvent(ActionEnum::REPAIR)
            ->setModifierRange(ReachEnum::INVENTORY)
        ;
        $gear1 = new Gear();
        $gear1->setModifierConfigs(new ArrayCollection([$modifier1]));
        $gearConfig1 = new ItemConfig();
        $gearConfig1
            ->setEquipmentName('gear1')
            ->setMechanics(new ArrayCollection([$gear1]))
        ;
        $gameGear1 = new GameItem($room);
        $gameGear1
            ->setName('gear1')
            ->setEquipment($gearConfig1)
        ;

        $modifier2 = new VariableEventModifierConfig('unitTestVariableEventModifier');
        $modifier2
            ->setTargetVariable(ModifierTargetEnum::PERCENTAGE)
            ->setDelta(1.5)
            ->setTargetEvent(ActionEnum::REPAIR)
            ->setModifierRange(ReachEnum::INVENTORY)
        ;
        $gear2 = new Gear();
        $gear2->setModifierConfigs(new ArrayCollection([$modifier2]));
        $gearConfig2 = new ItemConfig();
        $gearConfig2
            ->setEquipmentName('gear2')
            ->setMechanics(new ArrayCollection([$gear2]))
        ;
        $gameGear2 = new GameItem($room);
        $gameGear2
            ->setName('gear2')
            ->setEquipment($gearConfig2)
        ;
        $chargeStatus2 = new ChargeStatus($gameGear2, $chargeConfig);
        $chargeStatus2
            ->setCharge(1)
        ;

        $modifier3 = new VariableEventModifierConfig('unitTestVariableEventModifier');
        $modifier3
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(1)
            ->setTargetEvent(ActionEnum::REPAIR)
            ->setModifierRange(ReachEnum::INVENTORY)
        ;
        $gear3 = new Gear();
        $gear3->setModifierConfigs(new ArrayCollection([$modifier3]));
        $gearConfig3 = new ItemConfig();
        $gearConfig3
            ->setEquipmentName('gear3')
            ->setMechanics(new ArrayCollection([$gear3]))
        ;
        $gameGear3 = new GameItem($room);
        $gameGear3
            ->setName('gear3')
            ->setEquipment($gearConfig1)
        ;
        $chargeStatus3 = new ChargeStatus($gameGear3, $chargeConfig);
        $chargeStatus3
            ->setCharge(0)
        ;

        $room->addPlayer($player);

        $this->statusService->shouldReceive('updateCharge')
            ->andReturn($chargeStatus1)
            ->once()
        ;
        $this->service->applyChargeCost($player, ActionEnum::REPAIR);

        $this->statusService->shouldReceive('updateCharge')
            ->andReturn($chargeStatus1)
            ->once()
        ;
        $this->service->applyChargeCost($player, ActionEnum::REPAIR);
    }
}
