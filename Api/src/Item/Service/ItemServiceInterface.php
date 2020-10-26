<?php


namespace Mush\Item\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Item\Entity\Item;

interface ItemServiceInterface
{
    public function findByNameAndDaedalus(string $name, Daedalus $daedalus): Item;
}
