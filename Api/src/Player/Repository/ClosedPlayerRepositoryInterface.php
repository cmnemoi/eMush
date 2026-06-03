<?php

declare(strict_types=1);

namespace Mush\Player\Repository;

use Mush\Player\Entity\ClosedPlayer;

interface ClosedPlayerRepositoryInterface
{
    public function save(ClosedPlayer $closedPlayer): void;
}
