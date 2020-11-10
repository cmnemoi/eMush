<?php

namespace Mush\Item\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Enum\StatusEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Item\Entity\GameItem;
use Mush\Item\Entity\Item;
use Mush\Item\Entity\Items\Plant;
use Mush\Item\Entity\Items\Weapon;
use Mush\Item\Entity\ItemType;
use Mush\Item\Enum\ItemTypeEnum;
use Mush\Item\Repository\GameItemRepository;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\Status;
use Status\Enum\ChargeStrategyTypeEnum;

class GameItemService implements GameItemServiceInterface
{
    private EntityManagerInterface $entityManager;
    private GameItemRepository $repository;
    private ItemServiceInterface $itemService;
    private RandomServiceInterface $randomService;
    private ItemEffectServiceInterface $itemEffectService;

    public function __construct(
        EntityManagerInterface $entityManager,
        GameItemRepository $repository,
        ItemServiceInterface $itemService,
        RandomServiceInterface $randomService,
        ItemEffectServiceInterface $itemEffectService
    ) {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
        $this->itemService = $itemService;
        $this->randomService = $randomService;
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
                case ItemTypeEnum::WEAPON:
                    $this->initWeapon($gameItem, $type);
                    break;
            }
        }

        return $this->persist($gameItem);
    }

    private function initPlant(GameItem $gameItem, Plant $plant, Daedalus $daedalus): GameItem
    {
        $plantStatus = new Status();
        $plantStatus
            ->setName(StatusEnum::CHARGE)
            ->setStrategy(ChargeStrategyTypeEnum::PLANT)
            ->setThreshold($this->itemEffectService->getPlantEffect($plant, $daedalus)->getMaturationTime())
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setGameItem($gameItem)
            ->setCharge(0)
        ;

        $gameItem->addStatus($plantStatus);

        return $gameItem;
    }

    private function initWeapon(GameItem $gameItem, Weapon $weapon): GameItem
    {
        $chargeStatus = new Status();
        $chargeStatus
            ->setName(StatusEnum::CHARGE)
            ->setStrategy(ChargeStrategyTypeEnum::CYCLE_INCREMENT)
            ->setThreshold($weapon->getMaxCharges())
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setGameItem($gameItem)
            ->setCharge(0)
        ;

        $gameItem->addStatus($chargeStatus);

        return $gameItem;
    }
}
