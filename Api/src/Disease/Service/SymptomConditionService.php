<?php

namespace Mush\Disease\Service;

use Mush\Action\Entity\Action;
use Mush\Disease\Entity\Collection\SymptomConfigCollection;
use Mush\Disease\Entity\Config\SymptomCondition;
use Mush\Disease\Entity\Config\SymptomConfig;
use Mush\Disease\Enum\SymptomConditionEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Service\ModifierServiceInterface;
use Mush\Player\Entity\Player;

class SymptomConditionService implements SymptomConditionServiceInterface
{
    private ModifierServiceInterface $modifierService;
    private RandomServiceInterface $randomService;

    public function __construct(
        ModifierServiceInterface $modifierService,
        RandomServiceInterface $randomService,
    ) {
        $this->modifierService = $modifierService;
        $this->randomService = $randomService;
    }

    public function getActiveSymptoms(SymptomConfigCollection $symptomConfigs, Player $player, string $reason, ?Action $action = null): SymptomConfigCollection
    {
        $activeSymptoms = new SymptomConfigCollection();

        foreach ($symptomConfigs as $symptomConfig) {
            if ($this->checkSymptom($symptomConfig, $player, $reason, $action)) {
                $activeSymptoms->add($symptomConfig);
            }
        }

        return $activeSymptoms;
    }

    private function checkCondition(SymptomCondition $condition, Player $player, string $reason, ?Action $action): bool
    {
        switch ($condition->getName()) {
            case SymptomConditionEnum::ACTION_DIRTY_RATE:
                return $this->checkActionDirtyRateCondition($player, $action);

            case SymptomConditionEnum::ITEM_IN_ROOM:
                return $this->isItemInRoom($condition->getCondition(), $player);

            case SymptomConditionEnum::RANDOM:
                return $this->randomService->isSuccessful(intval($condition->getValue()));

            case SymptomConditionEnum::REASON:
                return $reason === $condition->getCondition();

            case SymptomConditionEnum::PLAYER_EQUIPMENT:
                return $this->checkPlayerEquipmentCondition($condition->getCondition(), $player);

            case SymptomConditionEnum::PLAYER_IN_ROOM:
                return $this->checkPlayerInRoomCondition($condition->getCondition(), $player);

            case SymptomConditionEnum::PLAYER_STATUS:
                return $this->checkPlayerStatusCondition($condition->getCondition(), $player);

            default:
                throw new \LogicException('this symptom condition is not implemented');
        }
    }

    private function checkSymptom(SymptomConfig $symptomConfig, Player $player, string $reason, ?Action $action): bool
    {
        foreach ($symptomConfig->getSymptomConditions() as $condition) {
            if (!$this->checkCondition($condition, $player, $reason, $action)) {
                return false;
            }
        }

        return true;
    }

    private function checkActionDirtyRateCondition(Player $player, ?Action $action): bool
    {
        if ($action === null) {
            throw new \LogicException('Provide an action for ACTION_DIRTY_RATE symptom condition');
        }

        $dirtyRate = $action->getDirtyRate();
        $isSuperDirty = $dirtyRate > 100;

        return $isSuperDirty ||
            $this->modifierService->isSuccessfulWithModifier(
                $player,
                $dirtyRate,
                [$action->getName()],
                false
            );
    }

    private function checkPlayerEquipmentCondition(?string $expectedEquipment, Player $player): bool
    {
        if ($expectedEquipment === null) {
            throw new \LogicException('Provide an equipment for PLAYER_EQUIPMENT symptom condition');
        }

        return $player->hasEquipmentByName($expectedEquipment);
    }

    private function checkPlayerInRoomCondition(?string $playerInRoomCondition, Player $player): bool
    {
        if ($playerInRoomCondition === null) {
            throw new \LogicException('Provide a condition on players for PLAYER_IN_ROOM symptom condition');
        }

        $playersInRoom = $player->getPlace()->getPlayers()->getPlayerAlive();

        switch ($playerInRoomCondition) {
            case SymptomConditionEnum::MUSH_IN_ROOM:
                foreach ($playersInRoom as $playerInRoom) {
                    if ($playerInRoom->isMush()) {
                        return true;
                    }
                }

                return false;

            case SymptomConditionEnum::NOT_ALONE:
                return count($playersInRoom) > 1;

            default:
                throw new \Exception('Unknown PLAYER_IN_ROOM condition');
        }
    }

    private function checkPlayerStatusCondition(?string $expectedStatus, Player $player): bool
    {
        if ($expectedStatus === null) {
            throw new \LogicException('Provide a status for PLAYER_STATUS symptom condition');
        }

        return $player->hasStatus($expectedStatus);
    }

    private function isItemInRoom(?string $item, Player $player): bool
    {
        if ($item === null) {
            throw new \LogicException('Provide an item name for ITEM_IN_ROOM symptom condition');
        }

        $placeEquipments = $player->getPlace()->getEquipments();
        foreach ($placeEquipments as $placeEquipment) {
            if ($placeEquipment->getName() === $item) {
                return true;
            }
        }

        return false;
    }
}
