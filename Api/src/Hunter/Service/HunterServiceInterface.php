<?php

namespace Mush\Hunter\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Entity\HunterCollection;
use Mush\Player\Entity\Player;

interface HunterServiceInterface
{
    public function changeVariable(string $variableName, Hunter $hunter, int $change, \DateTime $date, Player $author): void;

    public function updateDaedalusHunterPoints(Daedalus $daedalus): void;

    public function makeHuntersShoot(HunterCollection $attackingHunters): void;

    public function killHunter(Hunter $hunter): void;

    public function unpoolHunters(Daedalus $daedalus, \DateTime $time): void;
}
