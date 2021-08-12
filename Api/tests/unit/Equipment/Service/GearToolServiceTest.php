<?php

namespace Mush\Test\Equipment\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Entity\Mechanics\Tool;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Service\GearToolService;
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
    /** @var EventDispatcherInterface | Mockery\Mock */
    private EventDispatcherInterface $eventDispatcher;
    /** @var StatusServiceInterface | Mockery\Mock */
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
        $player->addItem($gameItem2);

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
        $player->addItem($gameItem2);

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
            ->setName(EquipmentStatusEnum::CHARGES)
            ->setCharge(3)
        ;

        $action->setName(ActionEnum::REPAIR);

        $usedTool = $this->service->getUsedTool($player, ActionEnum::REPAIR);
        $this->assertEquals($gameItem2, $usedTool);

        //Two tool with the same action but 1 is charged and the other have no charge left
        $chargeStatus2 = new ChargeStatus($gameItem2);
        $chargeStatus2
            ->setName(EquipmentStatusEnum::CHARGES)
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
            ->setName(EquipmentStatusEnum::CHARGES)
            ->setCharge(1)
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
