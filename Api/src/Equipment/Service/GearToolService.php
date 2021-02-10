<?php

namespace Mush\Equipment\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Player\Entity\Modifier;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\ModifierTargetEnum;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

class GearToolService implements GearToolServiceInterface
{
    public function getApplicableGears(Player $player, array $scopes, array $types, ?string $target = null): Collection
    {
        /** @var Collection $gears */
        $gears = new ArrayCollection();

        /** @var GameItem $item */
        foreach ($player->getItems() as $item) {
            /** @var Gear $gear */
            $gear = $item->getEquipment()->getMechanicByName(EquipmentMechanicEnum::GEAR);

            if ($gear &&
                in_array($gear->getModifier()->getScope(), $scopes) &&
                ($target === null || $gear->getModifier()->getTarget() === $target) &&
                (count($types) || in_array($gear->getModifier()->getTarget(), $types)) &&
                in_array($gear->getModifier()->getReach(), [ReachEnum::INVENTORY]) &&
                !$item->isBroken &&
                $item->isCharged()
            ) {
                $gears->add($gear);
            }
        }

        return $gears;
    }
    
    public function getEquipmentsOnReach(Player $player, string $reach = ReachEnum::SHELVE_NOT_HIDDEN): Collection
    {
        //reach can be set to inventory, shelve, shelve only or any room of the Daedalus
        switch ($reach){
            case ReachEnum::INVENTORY:
                return $player->getItems();

            case ReachEnum::SHELVE_NOT_HIDDEN:
                return new ArrayCollection(array_merge(
                    $player->getItems()->toArray(),
                    array_filter($player->getPlace()->getEquipments()->toArray(),
                        function(GameEquipment $gameEquipment) use ($player){
                            return (($hiddenStatus = $gameEquipment->getStatusByName(EquipmentStatusEnum::HIDDEN)) === null ||
                            $hiddenStatus->getTarget() === $player);
                        }
                    )
                ));

            case $reach === ReachEnum::SHELVE:
                return new ArrayCollection(array_merge(
                    $player->getItems()->toArray(),
                    $player->getPlace()->getEquipments()->toArray()));
            default:
                $room = $player->getDaedalus()->getPlaceByName($reach);
                if ($room === null) {
                    throw 'Invalid reach';
                }
                return $room
                    ->getEquipments();
        } 
    }


    public function getActionsTools(Player $player, array $scopes, array $targets): Collection
    {
        /** @var Collection $actions */
        $actions = new ArrayCollection();

        $tools = $this->getToolsOnReach($player);

        foreach ($tools as $tool) {
            /** @var Action $action */
            $actions = $tool->getEquipment()->getMechanicByName(EquipmentMechanicEnum::TOOL)->getAction();

            foreach ($actions as $action){
                if (
                    in_array($action->getScope(), $scopes) &&
                    in_array($action->getTarget(), $targets)
                ) {
                    $actions->add($action);
                }
            }
        }
        return $actions;
    }

    public function getToolsOnReach(Player $player): Collection
    {
        $equipments = $this->getEquipmentsOnReach($player);

        return $equipments->filter(fn (GameEquipment $equipment) => $equipment->getEquipment()->getMechanicByName(EquipmentMechanicEnum::TOOL));
    }

    public function getUsedTool(Player $player, string $actionName): Collection
    {
        $tools = $this->getToolsOnReach($player)->filter(
            fn (GameEquipment $tool) => 
            !$tool->getEquipment()->getMechanicByName(EquipmentMechanicEnum::TOOL)->getActions()
                ->filter(fn (Action $action) => $action->getName() === $actionName)->isEmpty()
        );


        if (!($noChargeTool = $tools->filter(fn (GameEquipment $tool) => 
            $tool->getStatusByName(EquipmentStatusEnum::CHARGES)===null))->isEmpty()
        ){
            return $noChargeTool->first();
        }

        if ($chargedTool = $tools->filter(fn (GameEquipment $tool) => 
                $chargeStatus = $tool->getStatusByName(EquipmentStatusEnum::CHARGES) && 
                $chargeStatus instanceof ChargeStatus &&
                $chargeStatus->getCharge() > 0)
        ){
            return $chargedTool->first();
        }

        throw 'No corresponding tool found';
    }
}