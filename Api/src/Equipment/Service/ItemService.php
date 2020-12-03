<?php

namespace Mush\Equipment\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Repository\ItemRepository;

class ItemService implements ItemServiceInterface
{
    private ItemRepository $itemRepository;

    /**
     * ItemService constructor.
     */
    public function __construct(ItemRepository $itemRepository)
    {
        $this->itemRepository = $itemRepository;
    }

    public function findByNameAndDaedalus(string $name, Daedalus $daedalus): ItemConfig
    {
        return $this->itemRepository->findByNameAndDaedalus($name, $daedalus);
    }
}
