<?php

namespace Mush\Tests\unit\Equipment\Service;

use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Service\GearToolService;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class GearToolServiceTest extends TestCase
{
    private GearToolService $service;

    /**
     * @before
     */
    public function before()
    {
        $this->service = new GearToolService();
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
            ->setEquipment($item);

        $item2 = new ItemConfig();
        $item2->setEquipmentName(ItemEnum::PLASTIC_SCRAPS);

        $gameItem2 = new GameItem($player);
        $gameItem2
            ->setName(ItemEnum::PLASTIC_SCRAPS)
            ->setEquipment($item2);

        $room
            ->addPlayer($player);

        $items = $this->service->getEquipmentsOnReach($player, ReachEnum::SHELVE);
        self::assertCount(2, $items);

        $items = $this->service->getEquipmentsOnReach($player, ReachEnum::INVENTORY);
        self::assertCount(1, $items);
        self::assertSame($gameItem2, $items->first());
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
            ->setEquipment($item);

        $room
            ->addPlayer($player);

        $items = $this->service->getEquipmentsOnReachByName($player, ItemEnum::PLASTIC_SCRAPS, ReachEnum::SHELVE);

        self::assertEmpty($items);

        $items = $this->service->getEquipmentsOnReachByName($player, ItemEnum::METAL_SCRAPS, ReachEnum::INVENTORY);

        self::assertEmpty($items);

        $items = $this->service->getEquipmentsOnReachByName($player, ItemEnum::METAL_SCRAPS, ReachEnum::SHELVE);

        self::assertNotEmpty($items);

        $hiddenConfig = new StatusConfig();
        $hiddenConfig->setStatusName(EquipmentStatusEnum::HIDDEN);
        $hidden = new Status($gameItem, $hiddenConfig);
        $hidden
            ->setTarget(new Player());

        $items = $this->service->getEquipmentsOnReachByName($player, ItemEnum::METAL_SCRAPS, ReachEnum::SHELVE_NOT_HIDDEN);
        self::assertEmpty($items);

        $items = $this->service->getEquipmentsOnReachByName($player, ItemEnum::METAL_SCRAPS, ReachEnum::SHELVE);
        self::assertNotEmpty($items);

        $gameItem2 = new GameItem($player);
        $gameItem2
            ->setName(ItemEnum::METAL_SCRAPS)
            ->setEquipment($item);

        $items = $this->service->getEquipmentsOnReachByName($player, ItemEnum::METAL_SCRAPS, ReachEnum::INVENTORY);
        self::assertCount(1, $items);

        $items = $this->service->getEquipmentsOnReachByName($player, ItemEnum::METAL_SCRAPS, ReachEnum::SHELVE);
        self::assertCount(2, $items);
    }
}
