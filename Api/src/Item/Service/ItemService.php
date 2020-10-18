<?php

namespace Mush\Item\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Item\Entity\Item;
use Mush\Item\Entity\ItemConfig;
use Mush\Item\Repository\ItemRepository;

class ItemService implements ItemServiceInterface
{
    private EntityManagerInterface $entityManager;

    private ItemRepository $repository;

    private ItemConfigServiceInterface $itemsConfig;

    public function __construct(
        EntityManagerInterface $entityManager,
        ItemRepository $repository,
        ItemConfigServiceInterface $itemsConfig
    ) {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
        $this->itemsConfig = $itemsConfig;
    }

    public function persist(Item $item): Item
    {
        $this->entityManager->persist($item);
        $this->entityManager->flush();

        return $item;
    }

    public function findById(int $id): ?Item
    {
        return $this->repository->find($id);
    }

    public function createItem(string $itemName): Item
    {

        /** @var ItemConfig $itemConfig */
        $itemConfig = $this->itemsConfig
            ->getConfigs()
            ->filter(function (ItemConfig $itemConfig) use ($itemName) {return $itemName === $itemConfig->getName();})
            ->first()
        ;

        $item = new Item();
        $item
            ->setName($itemConfig->getName())
            ->setType($itemConfig->getType())
            ->setIsDismantable($itemConfig->isDismantable())
            ->setIsFireBreakable($itemConfig->isFireBreakable())
            ->setIsFireDestroyable($itemConfig->isFireDestroyable())
            ->setIsMovable($itemConfig->isMovable())
            ->setIsHeavy($itemConfig->isHeavy())
            ->setIsHideable($itemConfig->isHideable())
            ->setIsStackable($itemConfig->isStackable())
        ;

        return $this->persist($item);
    }
}