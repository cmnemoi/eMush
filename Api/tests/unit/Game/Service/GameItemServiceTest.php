<?php

namespace Mush\Test\Game\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Item;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Repository\GameItemRepository;
use Mush\Equipment\Service\GameItemService;
use Mush\Equipment\Service\GameItemServiceInterface;
use Mush\Equipment\Service\ItemEffectService;
use Mush\Equipment\Service\ItemEffectServiceInterface;
use Mush\Equipment\Service\ItemServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Room;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\ItemStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use PHPUnit\Framework\TestCase;

class GameItemServiceTest extends TestCase
{
    /** @var EntityManagerInterface | Mockery\Mock */
    private EntityManagerInterface $entityManager;
    /** @var GameItemRepository | Mockery\Mock */
    private GameItemRepository $repository;
    /** @var ItemServiceInterface | Mockery\Mock */
    private ItemServiceInterface $itemService;
    /** @var StatusServiceInterface | Mockery\Mock */
    private StatusServiceInterface $statusService;
    /** @var ItemEffectServiceInterface | Mockery\Mock */
    private ItemEffectServiceInterface $itemEffectService;
    private GameItemServiceInterface $service;

    /**
     * @before
     */
    public function before()
    {
        $this->entityManager = Mockery::mock(EntityManagerInterface::class);
        $this->repository = Mockery::mock(GameItemRepository::class);
        $this->itemService = Mockery::mock(ItemServiceInterface::class);
        $this->statusService = Mockery::mock(StatusServiceInterface::class);
        $this->itemEffectService = Mockery::mock(ItemEffectService::class);

        $this->service = new GameItemService(
            $this->entityManager,
            $this->repository,
            $this->itemService,
            $this->statusService,
            $this->itemEffectService
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testGetOperationalItemByName()
    {
        $item = new Item();
        $item->setName(ItemEnum::METAL_SCRAPS);

        $room = new Room();

        $player = new Player();

        $gameItem = new GameItem();
        $gameItem
            ->setName(ItemEnum::METAL_SCRAPS)
            ->setItem($item)
        ;

        $room
            ->addItem($gameItem)
            ->addPlayer($player)
        ;

        $items = $this->service->getOperationalItemsByName(ItemEnum::PLASTIC_SCRAPS, $player, ReachEnum::SHELVE);

        $this->assertEmpty($items);

        $items = $this->service->getOperationalItemsByName(ItemEnum::PLASTIC_SCRAPS, $player, ReachEnum::INVENTORY);

        $this->assertEmpty($items);

        $items = $this->service->getOperationalItemsByName(ItemEnum::METAL_SCRAPS, $player, ReachEnum::SHELVE);

        $this->assertNotEmpty($items);

        $broken = new Status();
        $broken
            ->setName(ItemStatusEnum::BROKEN)
        ;
        $gameItem->addStatus($broken);
        $items = $this->service->getOperationalItemsByName(ItemEnum::METAL_SCRAPS, $player, ReachEnum::SHELVE);

        $this->assertEmpty($items);
    }
}
