<?php

namespace Mush\Hunter\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Entity\HunterCollection;
use Mush\Player\Entity\Player;

interface HunterServiceInterface
{   
    public function delete(array $entities): void;
    
    public function findById(int $id): ?Hunter;

    public function makeHuntersShoot(HunterCollection $attackingHunters): void;

    public function killHunter(Hunter $hunter, array $reasons, Player $author = null): void;

    public function persist(array $entities): void;

    public function unpoolHunters(Daedalus $daedalus, \DateTime $time): void;
}
