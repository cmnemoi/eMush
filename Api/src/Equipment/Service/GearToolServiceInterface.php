<?php

namespace Mush\Equipment\Service;

use Doctrine\Common\Collections\Collection;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Player\Entity\Player;

interface GearToolServiceInterface
{
    public function getActionsTools(Player $player, array $scopes, ?string $target = null): Collection;

    public function getUsedTool(Player $player, string $actionName): ?GameEquipment;

    public function applyChargeCost(Player $player, string $actionName, array $types = []): void;

    public function getEquipmentsOnReach(Player $player, string $reach = ReachEnum::SHELVE_NOT_HIDDEN): Collection;

    public function getEquipmentsOnReachByName(Player $player, string $equipmentName, string $reach = ReachEnum::SHELVE_NOT_HIDDEN): Collection;
}
