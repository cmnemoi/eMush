<?php

namespace Mush\Equipment\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Error;
use Mush\Action\Entity\Action;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\Mechanics\Tool;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Exception\LogicException;

class GearToolService implements GearToolServiceInterface
{
    private EventDispatcherInterface $eventDispatcher;
    private StatusServiceInterface $statusService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        StatusServiceInterface $statusService
    ) {
          $this->eventService = $eventDispatcher;
        $this->statusService = $statusService;
    }

    public function getEquipmentsOnReach(Player $player, string $reach = ReachEnum::SHELVE_NOT_HIDDEN): Collection
    {
        // reach can be set to inventory, shelve, shelve only or any room of the Daedalus
        switch ($reach) {
            case ReachEnum::INVENTORY:
                return $player->getEquipments();

            case ReachEnum::SHELVE_NOT_HIDDEN:
                return new ArrayCollection(array_merge(
                    $player->getEquipments()->toArray(),
                    array_filter($player->getPlace()->getEquipments()->toArray(),
                        function (GameEquipment $gameEquipment) use ($player) {
                            return ($hiddenStatus = $gameEquipment->getStatusByName(EquipmentStatusEnum::HIDDEN)) === null ||
                            $hiddenStatus->getTarget() === $player;
                        }
                    )
                ));

            case $reach === ReachEnum::SHELVE:
                return new ArrayCollection(array_merge(
                    $player->getEquipments()->toArray(),
                    $player->getPlace()->getEquipments()->toArray()));
            default:
                $room = $player->getDaedalus()->getPlaceByName($reach);
                if ($room === null) {
                    throw new Error('Invalid reach');
                }

                return $room
                    ->getEquipments();
        }
    }

    public function getEquipmentsOnReachByName(Player $player, string $equipmentName, string $reach = ReachEnum::SHELVE_NOT_HIDDEN): Collection
    {
        return $this->getEquipmentsOnReach($player, $reach)->filter(fn (GameEquipment $equipment) => $equipment->getName() === $equipmentName);
    }

    public function getActionsTools(Player $player, array $scopes, ?string $target = null): Collection
    {
        /** @var Collection $actions */
        $grantedActions = new ArrayCollection();

        $tools = $this->getToolsOnReach($player);

        foreach ($tools as $tool) {
            /** @var Action $action */
            $actions = $tool->getEquipment()->getMechanicByName(EquipmentMechanicEnum::TOOL)->getActions();

            foreach ($actions as $action) {
                if (in_array($action->getScope(), $scopes) &&
                    ($action->getTarget() === null || $action->getTarget() === $target)
                ) {
                    $grantedActions->add($action);
                }
            }
        }

        return $grantedActions;
    }

    private function getToolsOnReach(Player $player): Collection
    {
        $equipments = $this->getEquipmentsOnReach($player);

        return $equipments->filter(fn (GameEquipment $equipment) => $equipment->getEquipment()->getMechanicByName(EquipmentMechanicEnum::TOOL) !== null &&
            !$equipment->isBroken()
        );
    }

    public function getUsedTool(Player $player, string $actionName): ?GameEquipment
    {
        /** @var Collection $tools */
        $tools = new ArrayCollection();

        foreach ($this->getToolsOnReach($player) as $tool) {
            /** @var Tool $toolMechanic */
            $toolMechanic = $tool->getEquipment()->getMechanicByName(EquipmentMechanicEnum::TOOL);

            if ($toolMechanic &&
                !$toolMechanic->getActions()->filter(fn (Action $action) => $action->getName() === $actionName)->isEmpty()
            ) {
                $chargeStatus = $this->getChargeStatus($actionName, $tool);

                // if one tool provide this action for free prioritize it
                if ($chargeStatus === null) {
                    return $tool;
                } elseif ($chargeStatus->getCharge() > 0) {
                    $tools->add($tool);
                }
            }
        }

        if (!$tools->isEmpty()) {
            return $tools->first();
        }

        return null;
    }

    public function applyChargeCost(Player $player, string $actionName, array $types = []): void
    {
        $tool = $this->getUsedTool($player, $actionName);
        if ($tool) {
            $this->removeCharge($tool, $actionName);
        }
    }

    private function removeCharge(GameEquipment $equipment, string $actionName): void
    {
        $chargeStatus = $this->getChargeStatus($actionName, $equipment);

        if ($chargeStatus !== null) {
            $chargeStatus = $this->statusService->updateCharge($chargeStatus, -1);

            if ($chargeStatus === null) {
                $equipmentEvent = new EquipmentEvent(
                    $equipment,
                    false,
                    VisibilityEnum::HIDDEN,
                    EventEnum::OUT_OF_CHARGE,
                    new \DateTime()
                );
                $this->eventService->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);
            }
        }
    }

    private function getChargeStatus(string $actionName, GameEquipment $equipment): ?ChargeStatus
    {
        $charges = $equipment->getStatuses()->filter(function (Status $status) use ($actionName) {
            return $status instanceof ChargeStatus &&
                $status->getDischargeStrategy() === $actionName;
        });

        if ($charges->count() > 0) {
            return $charges->first();
        } elseif ($charges->count() === 0) {
            return null;
        } else {
            throw new LogicException('there should be maximum 1 chargeStatus with this dischargeStrategy on this statusHolder');
        }
    }
}
