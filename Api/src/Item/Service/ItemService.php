<?php

namespace Mush\Item\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Item\Entity\Item;
use Mush\Item\Repository\ItemRepository;

class ItemService implements ItemServiceInterface
{
    private ItemRepository $itemRepository;

    /**
     * ItemService constructor.
     * @param ItemRepository $itemRepository
     */
    public function __construct(ItemRepository $itemRepository)
    {
        $this->itemRepository = $itemRepository;
    }

    public function findByNameAndDaedalus(string $name, Daedalus $daedalus): Item
    {
        return $this->itemRepository->findByNameAndDaedalus($name, $daedalus);
    }
}
