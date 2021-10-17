<?php

namespace Mush\Test\Equipment\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Equipment\Entity\Config\GameItem;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\Config\Mechanics\Gear;
use Mush\Equipment\Entity\Config\Mechanics\Tool;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Service\GearToolService;
use Mush\Modifier\Entity\ModifierConfig;
use Mush\Modifier\Enum\ModifierTargetEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class GearToolServiceTest extends TestCase
{
    /** @var EventDispatcherInterface|Mockery\Mock */
    private EventDispatcherInterface $eventDispatcher;
    /** @var StatusServiceInterface|Mockery\Mock */
    private StatusServiceInterface $statusService;

    private GearToolService $service;

    /**
     * @before
     */
    public function before()
    {
        $this->eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $this->statusService = Mockery::mock(StatusServiceInterface::class);

        $this->service = new GearToolService(
            $this->eventDispatcher,
            $this->statusService
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testGetEquipmentOnReach()
    {
        $room = new Place();
        $player = new Player();

        $item = new ItemConfig();
        $item->setName(ItemEnum::METAL_SCRAPS);

        $gameItem = new GameItem();
        $gameItem
            ->setName(ItemEnum::METAL_SCRAPS)
            ->setEquipment($item)
        ;

        $item2 = new ItemConfig();
        $item2->setName(ItemEnum::PLASTIC_SCRAPS);

        $gameItem2 = new GameItem();
        $gameItem2
            ->setName(ItemEnum::PLASTIC_SCRAPS)
            ->setEquipment($item2)
        ;

        $room
            ->addEquipment($gameItem)
            ->addPlayer($player)
        ;
        $player->addEquipment($gameItem2);

        $items = $this->service->getEquipmentsOnReach($player, ReachEnum::SHELVE);
        $this->assertCount(2, $items);

        $items = $this->service->getEquipmentsOnReach($player, ReachEnum::INVENTORY);
        $this->assertCount(1, $items);
        $this->assertEquals($gameItem2, $items->first());
    }

    public function testGetEquipmentsOnReachByName()
    {
        $item = new ItemConfig();
        $item->setName(ItemEnum::METAL_SCRAPS);

        $room = new Place();

        $player = new Player();

        $gameItem = new GameItem();
        $gameItem
            ->setName(ItemEnum::METAL_SCRAPS)
            ->setEquipment($item)
        ;

        $room
            ->addEquipment($gameItem)
            ->addPlayer($player)
        ;

        $items = $this->service->getEquipmentsOnReachByName($player, ItemEnum::PLASTIC_SCRAPS, ReachEnum::SHELVE);

        $this->assertEmpty($items);

        $items = $this->service->getEquipmentsOnReachByName($player, ItemEnum::METAL_SCRAPS, ReachEnum::INVENTORY);

        $this->assertEmpty($items);

        $items = $this->service->getEquipmentsOnReachByName($player, ItemEnum::METAL_SCRAPS, ReachEnum::SHELVE);

        $this->assertNotEmpty($items);

        $hidden = new Status($gameItem);
        $hidden
            ->setName(EquipmentStatusEnum::HIDDEN)
            ->setTarget(new Player())
        ;

        $items = $this->service->getEquipmentsOnReachByName($player, ItemEnum::METAL_SCRAPS, ReachEnum::SHELVE_NOT_HIDDEN);
        $this->assertEmpty($items);

        $items = $this->service->getEquipmentsOnReachByName($player, ItemEnum::METAL_SCRAPS, ReachEnum::SHELVE);
        $this->assertNotEmpty($items);

        $gameItem2 = new GameItem();
        $gameItem2
            ->setName(ItemEnum::METAL_SCRAPS)
            ->setEquipment($item)
        ;
        $player->addEquipment($gameItem2);

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
            ->setName(ActionEnum::SHOWER)
            ->setScope(ActionScopeEnum::CURRENT)
        ;

        $tool = new Tool();
        $tool->setActions(new arrayCollection([$action]));

        $item = new ItemConfig();
        $item
            ->setName(ItemEnum::METAL_SCRAPS)
            ->setMechanics(new arrayCollection([$tool]))
        ;

        $gameItem = new GameItem();
        $gameItem
            ->setName(ItemEnum::METAL_SCRAPS)
            ->setEquipment($item)
        ;

        $room
            ->addPlayer($player)
            ->addEquipment($gameItem)
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
            ->setName(ActionEnum::SHOWER)
            ->setScope(ActionScopeEnum::CURRENT)
        ;

        $tool = new Tool();
        $tool->setActions(new arrayCollection([$action]));

        $item = new ItemConfig();
        $item
            ->setName(ItemEnum::METAL_SCRAPS)
            ->setMechanics(new arrayCollection([$tool]))
        ;

        $gameItem = new GameItem();
        $gameItem
            ->setName(ItemEnum::METAL_SCRAPS)
            ->setEquipment($item)
        ;

        $room
            ->addPlayer($player)
            ->addEquipment($gameItem)
        ;

        $action2 = new Action();
        $action2
            ->setName(ActionEnum::REPAIR)
            ->setScope(ActionScopeEnum::ROOM)
        ;

        $tool2 = new Tool();
        $tool2->setActions(new arrayCollection([$action2]));

        $item2 = new ItemConfig();
        $item2
            ->setName(ItemEnum::METAL_SCRAPS)
            ->setMechanics(new arrayCollection([$tool2]))
        ;

        $gameItem2 = new GameItem();
        $gameItem2
            ->setName(ItemEnum::PLASTIC_SCRAPS)
            ->setEquipment($item2)
        ;
        $room->addEquipment($gameItem2);

        $usedTool = $this->service->getUsedTool($player, ActionEnum::TAKE);
        $this->assertNull($usedTool);

        $usedTool = $this->service->getUsedTool($player, ActionEnum::REPAIR);
        $this->assertEquals($gameItem2, $usedTool);

        //Two tool with the same action but 1 with charges
        $chargeStatus = new ChargeStatus($gameItem);
        $chargeStatus
            ->setCharge(3)
            ->setDischargeStrategy(ActionEnum::REPAIR)
        ;

        $action->setName(ActionEnum::REPAIR);

        $usedTool = $this->service->getUsedTool($player, ActionEnum::REPAIR);
        $this->assertEquals($gameItem2, $usedTool);

        //Two tool with the same action but 1 is charged and the other have no charge left
        $chargeStatus2 = new ChargeStatus($gameItem2);
        $chargeStatus2
            ->setCharge(0)
            ->setDischargeStrategy(ActionEnum::REPAIR)

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
            ->setName(ActionEnum::REPAIR)
            ->setScope(ActionScopeEnum::CURRENT)
        ;
        $tool = new Tool();
        $tool->setActions(new arrayCollection([$action]));
        $toolConfig = new ItemConfig();
        $toolConfig
            ->setName('tool')
            ->setMechanics(new arrayCollection([$tool]))
        ;
        $gameTool = new GameItem();
        $gameTool
            ->setName('tool')
            ->setEquipment($toolConfig)
        ;
        $chargeStatus1 = new ChargeStatus($gameTool);
        $chargeStatus1
            ->setCharge(1)
            ->setDischargeStrategy(ActionEnum::REPAIR)
        ;

        $modifier1 = new ModifierConfig();
        $modifier1
            ->setTarget(ModifierTargetEnum::PERCENTAGE)
            ->setDelta(1.5)
            ->setScope(ActionEnum::REPAIR)
            ->setReach(ReachEnum::INVENTORY)
        ;
        $gear1 = new Gear();
        $gear1->setModifierConfigs(new arrayCollection([$modifier1]));
        $gearConfig1 = new ItemConfig();
        $gearConfig1
            ->setName('gear1')
            ->setMechanics(new arrayCollection([$gear1]))
        ;
        $gameGear1 = new GameItem();
        $gameGear1
            ->setName('gear1')
            ->setEquipment($gearConfig1)
        ;

        $modifier2 = new ModifierConfig();
        $modifier2
            ->setTarget(ModifierTargetEnum::PERCENTAGE)
            ->setDelta(1.5)
            ->setScope(ActionEnum::REPAIR)
            ->setReach(ReachEnum::INVENTORY)
        ;
        $gear2 = new Gear();
        $gear2->setModifierConfigs(new arrayCollection([$modifier2]));
        $gearConfig2 = new ItemConfig();
        $gearConfig2
            ->setName('gear2')
            ->setMechanics(new arrayCollection([$gear2]))
        ;
        $gameGear2 = new GameItem();
        $gameGear2
            ->setName('gear2')
            ->setEquipment($gearConfig2)
        ;
        $chargeStatus2 = new ChargeStatus($gameGear2);
        $chargeStatus2
            ->setCharge(1)
            ->setDischargeStrategy(ActionEnum::REPAIR)
        ;

        $modifier3 = new ModifierConfig();
        $modifier3
            ->setTarget(ModifierTargetEnum::ACTION_POINT)
            ->setDelta(1)
            ->setScope(ActionEnum::REPAIR)
            ->setReach(ReachEnum::INVENTORY)
        ;
        $gear3 = new Gear();
        $gear3->setModifierConfigs(new arrayCollection([$modifier3]));
        $gearConfig3 = new ItemConfig();
        $gearConfig3
            ->setName('gear3')
            ->setMechanics(new arrayCollection([$gear3]))
        ;
        $gameGear3 = new GameItem();
        $gameGear3
            ->setName('gear3')
            ->setEquipment($gearConfig1)
        ;
        $chargeStatus3 = new ChargeStatus($gameGear3);
        $chargeStatus3
            ->setCharge(0)
            ->setDischargeStrategy(ActionEnum::REPAIR)
        ;

        $room->addPlayer($player)->addEquipment($gameTool);

        $this->statusService->shouldReceive('updateCharge')
            ->with($chargeStatus1, -1)
            ->andReturn($chargeStatus1)
            ->once()
        ;
        $this->service->applyChargeCost($player, ActionEnum::REPAIR);

        $this->statusService->shouldReceive('updateCharge')
            ->with($chargeStatus1, -1)
            ->andReturn($chargeStatus1)
            ->once()
        ;
        $this->service->applyChargeCost($player, ActionEnum::REPAIR);
    }
}
