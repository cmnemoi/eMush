<?php

namespace Mush\Player\Service;

use Mush\Player\Entity\Modifier;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\ModifierTargetEnum;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

class ActionModifierService implements ActionModifierServiceInterface
{
    public function getActionModifier(Player $player, array $scope, array $types, ?string $target = null): Collection
    {
        $gearsModifiers = $this->getApplicableGears($player, $scope, $type, $target)
    }

    public function getApplicableGears(Player $player, array $scope, array $types, ?string $target = null): Collection
    {
        /** @var Collection $gears */
        $gears = new ArrayCollection();
        /** @var GameItem $item */
        foreach ($player->getItems() as $item) {
            /** @var Gear $gear */
            $gear = $item->getEquipment()->getMechanicByName(EquipmentMechanicEnum::GEAR);

            if ($gear &&
                in_array($gear->getModifier()->getScope(), $scope) &&
                ($target === null || $gear->getModifier()->getTarget() === $target) &&
                (count($types) || in_array($gear->getModifier()->getTarget(), $types)) &&
                in_array($gear->getModifier()->getReach(), [ReachEnum::INVENTORY]) &&
                !$item->isBroken() &&
                !($chargeStatus = $item->getStatusByName(EquipmentStatusEnum::CHARGES) && $chargeStatus->getCharge() ===0)
            ) {
                $gears->add($gear);
            }
        }

        return $gears;
    }
}
