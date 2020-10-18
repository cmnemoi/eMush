<?php

namespace Mush\Item\Service;

use Mush\Item\Entity\Item;

interface ItemServiceInterface
{
    public function persist(Item $item): Item;

    public function findById(int $id): ?Item;

    public function createItem(string $itemName): Item;
}