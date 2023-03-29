<?php

namespace Mush\Hunter\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Hunter\Entity\HunterCollection;

interface HunterServiceInterface
{
    public function makeHuntersShoot(HunterCollection $hunters): void;

    public function putHuntersInPool(Daedalus $daedalus, int $nbHuntersToPutInPool): HunterCollection;

    public function unpoolHunters(Daedalus $daedalus, int $nbHuntersToUnpool): void;
}
