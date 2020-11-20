<?php

namespace Mush\Item\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Item\Entity\GameItem;
use Mush\Item\Entity\Item;
use Mush\Item\Entity\Items\Plant;
use Mush\Item\Entity\Items\Charged;
use Mush\Item\Entity\Items\Weapon;
use Mush\Item\Entity\ItemType;
use Mush\Item\Enum\ItemTypeEnum;
use Mush\Item\Repository\GameItemRepository;
use Mush\Status\Enum\ItemStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Status\Enum\ChargeStrategyTypeEnum;

class GameItemService implements GameItemServiceInterface
{
    private EntityManagerInterface $entityManager;
    private GameItemRepository $repository;
    private ItemServiceInterface $itemService;
    private RandomServiceInterface $randomService;
    private StatusServiceInterface $statusService;
    private ItemEffectServiceInterface $itemEffectService;

    public function __construct(
        EntityManagerInterface $entityManager,
        GameItemRepository $repository,
        ItemServiceInterface $itemService,
        RandomServiceInterface $randomService,
        StatusServiceInterface $statusService,
        ItemEffectServiceInterface $itemEffectService
    ) {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
        $this->itemService = $itemService;
        $this->randomService = $randomService;
        $this->statusService = $statusService;
        $this->itemEffectService = $itemEffectService;
    }

    public function persist(GameItem $item): GameItem
    {
        $this->entityManager->persist($item);
        $this->entityManager->flush();

        return $item;
    }

    public function delete(GameItem $item): void
    {
        $this->entityManager->remove($item);
        $this->entityManager->flush();
    }

    public function findById(int $id): ?GameItem
    {
        return $this->repository->find($id);
    }

    public function createGameItemFromName(string $itemName, Daedalus $daedalus): GameItem
    {
        $item = $this->itemService->findByNameAndDaedalus($itemName, $daedalus);

        return $this->createGameItem($item, $daedalus);
    }

    public function createGameItem(Item $item, Daedalus $daedalus): GameItem
    {
        $gameItem = $item->createGameItem();

        /** @var ItemType $type */
        foreach ($item->getTypes() as $type) {
            switch ($type->getType()) {
                case ItemTypeEnum::PLANT:
                    $this->initPlant($gameItem, $type, $daedalus);
                    break;
                case ItemTypeEnum::CHARGED:
                    $this->initCharged($gameItem, $type);
                    break;
            }
        }

        return $this->persist($gameItem);
    }

     // @TODO maybe remove those init functions to directly include them in createGameItem
    private function initPlant(GameItem $gameItem, Plant $plant, Daedalus $daedalus): GameItem
    {
        $plantStatus = $this->statusService->createChargeItemStatus(
            ItemStatusEnum::PLANT_YOUNG,
            $gameItem,
            ChargeStrategyTypeEnum::PLANT,
            0,
            $this->itemEffectService->getPlantEffect($plant, $daedalus)->getMaturationTime()
        );

        return $gameItem;
    }

    private function initCharged(GameItem $gameItem, Charged $charged): GameItem
    {
        $chargeStatus = $this->statusService->createChargeItemStatus(
            StatusEnum::CHARGE,
            $gameItem,
            $charged->getChargeStrategy(),
            $charged->getStartCharge(),
            $charged->getMaxCharge()
        );

        return $gameItem;
    }
}
