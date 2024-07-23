<?php

declare(strict_types=1);

namespace Mush\Player\Repository;

use Mush\Player\Entity\Player;

interface PlayerRepositoryInterface
{
    public function save(Player $player): void;
}
