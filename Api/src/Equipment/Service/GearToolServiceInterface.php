<?php

namespace Mush\Equipment\Service;

use Doctrine\Common\Collections\Collection;
use Mush\Player\Entity\Player;

interface GearToolServiceInterface
{
    public function getApplicableGears(Player $player, array $scopes, array $types, ?string $target = null): Collection;

    public function getActionsTools(Player $player, array $scopes, array $targets): Collection;
}