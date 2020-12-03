<?php

namespace Mush\Equipment\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\ItemConfig;

interface ItemServiceInterface
{
    public function findByNameAndDaedalus(string $name, Daedalus $daedalus): ItemConfig;
}
