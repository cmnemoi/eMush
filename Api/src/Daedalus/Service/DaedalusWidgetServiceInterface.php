<?php

namespace Mush\Daedalus\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Player\Entity\Player;

interface DaedalusWidgetServiceInterface
{
    public function getMinimap(Daedalus $daedalus, Player $player): array;
}
