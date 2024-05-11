<?php

namespace Mush\Equipment\Service;

use Doctrine\Common\Collections\Collection;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Player\Entity\Player;

interface GearToolServiceInterface
{
    public function getEquipmentsOnReach(Player $player, string $reach = ReachEnum::SHELVE_NOT_HIDDEN): Collection;

    public function getEquipmentsOnReachByName(Player $player, string $equipmentName, string $reach = ReachEnum::SHELVE_NOT_HIDDEN): Collection;
}
