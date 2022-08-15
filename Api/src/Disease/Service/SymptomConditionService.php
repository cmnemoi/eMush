<?php

namespace Mush\Disease\Service;

use Mush\Disease\Entity\Collection\SymptomConfigCollection;
use Mush\Disease\Entity\Config\SymptomConfig;
use Mush\Disease\Entity\SymptomCondition;
use Mush\Disease\Enum\SymptomConditionEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;

class SymptomConditionService implements SymptomConditionServiceInterface
{
    private RandomServiceInterface $randomService;

    public function __construct(
        RandomServiceInterface $randomService,
    ) {
        $this->randomService = $randomService;
    }

    public function getActiveSymptoms(SymptomConfigCollection $symptomConfigs, Player $player, string $reason): SymptomConfigCollection
    {
        $activeSymptoms = new SymptomConfigCollection();

        foreach ($symptomConfigs as $symptomConfig) {
            if ($this->checkSymptom($symptomConfig, $player, $reason)) {
                $activeSymptoms->add($symptomConfig);
            }
        }

        return $activeSymptoms;
    }

    private function checkPlayerInRoomCondition(?string $playerInRoomCondition, Player $player): bool
    {
        if ($playerInRoomCondition === null) {
            throw new \LogicException('Provide a condition on players for PLAYER_IN_ROOM symptom condition');
        }

        switch ($playerInRoomCondition) {
            case SymptomConditionEnum::MUSH_IN_ROOM:
                $playersInRoom = $player->getPlace()->getPlayers()->getPlayerAlive();

                foreach ($playersInRoom as $playerInRoom) {
                    if ($playerInRoom->isMush()) {
                        return true;
                    }
                }

                return false;

            case SymptomConditionEnum::NOT_ALONE:
                $playersInRoom = $player->getPlace()->getPlayers()->getPlayerAlive();

                return count($playersInRoom) > 1;

            default:
                throw new \Exception('Unknown PLAYER_IN_ROOM condition');
        }
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

    private function checkCondition(SymptomCondition $condition, Player $player, string $reason): bool
    {
        switch ($condition->getName()) {
            case SymptomConditionEnum::ITEM_IN_ROOM:
                return $this->isItemInRoom($condition->getCondition(), $player);

            case SymptomConditionEnum::RANDOM:
                return $this->randomService->isSuccessful(intval($condition->getValue()));

            case SymptomConditionEnum::REASON:
                return $condition->getCondition() === null || $reason === $condition->getCondition();

            case SymptomConditionEnum::PLAYER_EQUIPMENT:
                $expectedEquipment = $condition->getCondition();

                if ($expectedEquipment === null) {
                    throw new \LogicException('Provide an item name for PLAYER_EQUIPMENT symptom condition');
                }

                return $player->hasEquipmentByName($expectedEquipment);

            case SymptomConditionEnum::PLAYER_IN_ROOM:
                return $this->checkPlayerInRoomCondition($condition->getCondition(), $player);

            default:
                throw new \LogicException('this symptom condition is not implemented');
        }
    }

    private function checkSymptom(SymptomConfig $symptomConfig, Player $player, string $reason): bool
    {
        foreach ($symptomConfig->getSymptomConditions() as $condition) {
            if (!$this->checkCondition($condition, $player, $reason)) {
                return false;
            }
        }

        return true;
    }
}
