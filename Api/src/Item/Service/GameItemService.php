<?php

namespace Mush\Item\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Item\Entity\GameItem;
use Mush\Item\Entity\Item;
use Mush\Item\Repository\GameItemRepository;

class GameItemService implements GameItemServiceInterface
{
    private EntityManagerInterface $entityManager;
    private GameItemRepository $repository;
    private ItemServiceInterface $itemService;
    private RandomServiceInterface $randomService;

    public function __construct(
        EntityManagerInterface $entityManager,
        GameItemRepository $repository,
        ItemServiceInterface $itemService,
        RandomServiceInterface $randomService
    ) {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
        $this->itemService = $itemService;
        $this->randomService = $randomService;
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

        return $this->createGameItem($item);
    }

    public function createGameItem(Item $item): GameItem
    {
        $gameItem = new GameItem();
        $gameItem
            ->setName($item->getName())
            ->setStatuses([])
            ->setItem($item)
        ;

        return $this->persist($gameItem);
    }


}
