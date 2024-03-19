<?php

declare(strict_types=1);

namespace Mush\Exploration\Service;

use Mush\Exploration\Entity\Exploration;
use Mush\Player\Entity\Player;

interface AddPlayerToExplorationTeamServiceInterface
{
    public function execute(Player $player, Exploration $exploration): void;
}
