<?php

namespace Mush\Test\Equipment\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Repository\GameEquipmentRepository;
use Mush\Equipment\Service\EquipmentEffectServiceInterface;
use Mush\Equipment\Service\EquipmentServiceInterface;
use Mush\Equipment\Service\GearToolService;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Status\Entity\Status;
use Mush\Status\Service\StatusServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class GearToolServiceTest extends TestCase
{
    /** @var EventDispatcherInterface | Mockery\Mock */
    private EventDispatcherInterface $eventDispatcher;
    /** @var EntityManagerInterface | Mockery\Mock */
    private EntityManagerInterface $entityManager;
    /** @var GameEquipmentRepository | Mockery\Mock */
    private GameEquipmentRepository $repository;
    /** @var RandomServiceInterface | Mockery\Mock */
    private RandomServiceInterface $randomService;
    /** @var EquipmentServiceInterface | Mockery\Mock */
    private EquipmentServiceInterface $equipmentService;
    /** @var EquipmentEffectServiceInterface | Mockery\Mock */
    private EquipmentEffectServiceInterface $equipmentEffectService;
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

    /* public function testGetOperationalEquipmentByName()
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

        $items = $this->service->getOperationalEquipmentsByName(ItemEnum::PLASTIC_SCRAPS, $player, ReachEnum::SHELVE);

        $this->assertEmpty($items);

        $items = $this->service->getOperationalEquipmentsByName(ItemEnum::PLASTIC_SCRAPS, $player, ReachEnum::INVENTORY);

        $this->assertEmpty($items);

        $items = $this->service->getOperationalEquipmentsByName(ItemEnum::METAL_SCRAPS, $player, ReachEnum::SHELVE);

        $this->assertNotEmpty($items);

        $broken = new Status($gameItem);
        $broken
            ->setName(EquipmentStatusEnum::BROKEN)
        ;

        $items = $this->service->getOperationalEquipmentsByName(ItemEnum::METAL_SCRAPS, $player, ReachEnum::SHELVE);

        $this->assertEmpty($items);
    } */
}
