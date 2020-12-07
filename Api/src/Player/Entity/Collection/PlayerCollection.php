<?php

namespace Mush\Player\Entity\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Player\Entity\Player;

class PlayerCollection extends ArrayCollection
{
    public function getPlayerAlive(): PlayerCollection
    {
        return $this->filter(fn (Player $player) => $player->isAlive());
    }
}
