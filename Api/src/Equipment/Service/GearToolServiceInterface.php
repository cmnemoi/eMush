<?php

namespace Mush\Equipment\Service;

use Doctrine\Common\Collections\Collection;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Player\Entity\Player;

interface GearToolServiceInterface
{
    public function getApplicableGears(Player $player, array $scopes, array $types, ?string $target = null): Collection;

    public function getActionsTools(Player $player, array $scopes, ?string $target = null): Collection;

    public function getUsedTool(Player $player, string $actionName): ?GameEquipment;

    public function applyChargeCost(GameEquipment $equipment): void;

    public function getEquipmentsOnReach(Player $player, string $reach = ReachEnum::SHELVE_NOT_HIDDEN): Collection;

    public function getEquipmentsOnReachByName(Player $player, string $equipmentName, string $reach = ReachEnum::SHELVE_NOT_HIDDEN): Collection;
}
