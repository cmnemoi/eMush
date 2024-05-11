<?php

namespace Mush\Equipment\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\EquipmentStatusEnum;

class GearToolService implements GearToolServiceInterface
{
    public function getEquipmentsOnReach(Player $player, string $reach = ReachEnum::SHELVE_NOT_HIDDEN): Collection
    {
        // reach can be set to inventory, shelve, shelve only or any room of the Daedalus
        switch ($reach) {
            case ReachEnum::INVENTORY:
                return $player->getEquipments();

            case ReachEnum::SHELVE_NOT_HIDDEN:
                return new ArrayCollection(array_merge(
                    $player->getEquipments()->toArray(),
                    array_filter(
                        $player->getPlace()->getEquipments()->toArray(),
                        static function (GameEquipment $gameEquipment) use ($player) {
                            return ($hiddenStatus = $gameEquipment->getStatusByName(EquipmentStatusEnum::HIDDEN)) === null
                            || $hiddenStatus->getTarget() === $player;
                        }
                    )
                ));

            case $reach === ReachEnum::SHELVE:
                return new ArrayCollection(array_merge(
                    $player->getEquipments()->toArray(),
                    $player->getPlace()->getEquipments()->toArray()
                ));

            default:
                $room = $player->getDaedalus()->getPlaceByName($reach);
                if ($room === null) {
                    throw new \Exception("This reach {$reach} is not handled");
                }

                return $room
                    ->getEquipments();
        }
    }

    public function getEquipmentsOnReachByName(Player $player, string $equipmentName, string $reach = ReachEnum::SHELVE_NOT_HIDDEN): Collection
    {
        return $this->getEquipmentsOnReach($player, $reach)->filter(static fn (GameEquipment $equipment) => $equipment->getName() === $equipmentName);
    }
}
