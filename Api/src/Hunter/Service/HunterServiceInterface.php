<?php

namespace Mush\Hunter\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Entity\HunterCollection;

interface HunterServiceInterface
{   
    public function findById(int $id): ?Hunter;

    public function makeHuntersShoot(HunterCollection $attackingHunters): void;

    public function killHunter(Hunter $hunter): void;

    public function persist(array $entities): void;

    public function unpoolHunters(Daedalus $daedalus, \DateTime $time): void;
}
