<?php

namespace Mush\Test\Game\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Repository\GameEquipmentRepository;
use Mush\Equipment\Service\EquipmentEffectService;
use Mush\Equipment\Service\EquipmentEffectServiceInterface;
use Mush\Equipment\Service\EquipmentServiceInterface;
use Mush\Equipment\Service\GameEquipmentService;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Room;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class GameEquipmentServiceTest extends TestCase
{
    /** @var EntityManagerInterface | Mockery\Mock */
    private EntityManagerInterface $entityManager;
    /** @var GameEquipmentRepository | Mockery\Mock */
    private GameEquipmentRepository $repository;
    /** @var EquipmentServiceInterface | Mockery\Mock */
    private EquipmentServiceInterface $equipmentService;
    /** @var StatusServiceInterface | Mockery\Mock */
    private StatusServiceInterface $statusService;
    /** @var EquipmentEffectServiceInterface | Mockery\Mock */
    private EquipmentEffectServiceInterface $equipmentEffectService;
    private GameEquipmentServiceInterface $service;
    /** @var RandomServiceInterface | Mockery\Mock */
    private RandomServiceInterface $randomService;
    /** @var EventDispatcherInterface | Mockery\Mock */
    protected EventDispatcherInterface $eventDispatcher;

    /**
     * @before
     */
    public function before()
    {
        $this->entityManager = Mockery::mock(EntityManagerInterface::class);
        $this->repository = Mockery::mock(GameEquipmentRepository::class);
        $this->equipmentService = Mockery::mock(EquipmentServiceInterface::class);
        $this->statusService = Mockery::mock(StatusServiceInterface::class);
        $this->equipmentEffectService = Mockery::mock(EquipmentEffectService::class);
        $this->randomService = Mockery::mock(RandomServiceInterface::class);

        $this->eventDispatcher = Mockery::mock(EventDispatcherInterface::class);

        $this->service = new GameEquipmentService(
            $this->entityManager,
            $this->repository,
            $this->equipmentService,
            $this->statusService,
            $this->equipmentEffectService,
            $this->randomService,
            $this->eventDispatcher
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
