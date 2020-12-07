<?php

namespace Mush\Test\Game\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Repository\GameItemRepository;
use Mush\Equipment\Service\GameEquipmentService;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Equipment\Service\EquipmentEffectService;
use Mush\Equipment\Service\EquipmentEffectServiceInterface;
use Mush\Equipment\Service\ItemServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Room;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use PHPUnit\Framework\TestCase;

class GameEquipmentServiceTest extends TestCase
{
    /** @var EntityManagerInterface | Mockery\Mock */
    private EntityManagerInterface $entityManager;
    /** @var GameItemRepository | Mockery\Mock */
    private GameItemRepository $repository;
    /** @var ItemServiceInterface | Mockery\Mock */
    private ItemServiceInterface $itemService;
    /** @var StatusServiceInterface | Mockery\Mock */
    private StatusServiceInterface $statusService;
    /** @var EquipmentEffectServiceInterface | Mockery\Mock */
    private EquipmentEffectServiceInterface $equipmentEffectService;
    private GameEquipmentServiceInterface $service;

    /**
     * @before
     */
    public function before()
    {
        $this->entityManager = Mockery::mock(EntityManagerInterface::class);
        $this->repository = Mockery::mock(GameItemRepository::class);
        $this->equipmentService = Mockery::mock(EquipmentServiceInterface::class);
        $this->statusService = Mockery::mock(StatusServiceInterface::class);
        $this->equipmentEffectService = Mockery::mock(EquipmentEffectService::class);

        $this->service = new GameEquipmentService(
            $this->entityManager,
            $this->repository,
            $this->equipmentService,
            $this->statusService,
            $this->equipmentEffectService
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testGetOperationalEquipmentByName()
    {
        $item = new ItemConfig();
        $item->setName(ItemEnum::METAL_SCRAPS);

        $room = new Room();

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

        $items = $this->service->getOperationalEquipmentsByName(ItemEnum::PLASTIC_SCRAPS, $player, ReachEnum::SHELVE);

        $this->assertEmpty($items);

        $items = $this->service->getOperationalEquipmentsByName(ItemEnum::PLASTIC_SCRAPS, $player, ReachEnum::INVENTORY);

        $this->assertEmpty($items);

        $items = $this->service->getOperationalEquipmentsByName(ItemEnum::METAL_SCRAPS, $player, ReachEnum::SHELVE);

        $this->assertNotEmpty($items);

        $broken = new Status();
        $broken
            ->setName(EquipmentStatusEnum::BROKEN)
        ;
        $gameItem->addStatus($broken);
        $items = $this->service->getOperationalEquipmentsByName(ItemEnum::METAL_SCRAPS, $player, ReachEnum::SHELVE);

        $this->assertEmpty($items);
    }
}
