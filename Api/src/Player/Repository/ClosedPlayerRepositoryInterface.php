<?php

namespace Mush\Player\Repository;

use Mush\Player\Entity\ClosedPlayer;

interface ClosedPlayerRepositoryInterface
{
    public function save(ClosedPlayer $closedPlayer): void;
}
