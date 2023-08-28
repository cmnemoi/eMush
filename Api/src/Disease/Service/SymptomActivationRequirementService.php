<?php

namespace Mush\Disease\Service;

use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Action\Event\ActionVariableEvent;
use Mush\Disease\Entity\Collection\SymptomConfigCollection;
use Mush\Disease\Entity\Config\SymptomActivationRequirement;
use Mush\Disease\Entity\Config\SymptomConfig;
use Mush\Disease\Enum\SymptomActivationRequirementEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;

class SymptomActivationRequirementService implements SymptomActivationRequirementServiceInterface
{
    private EventServiceInterface $eventService;
    private RandomServiceInterface $randomService;

    public function __construct(
        EventServiceInterface $eventService,
        RandomServiceInterface $randomService,
    ) {
        $this->eventService = $eventService;
        $this->randomService = $randomService;
    }

    public function getActiveSymptoms(SymptomConfigCollection $symptomConfigs, Player $player, array $tags, Action $action = null): SymptomConfigCollection
    {
        $activeSymptoms = new SymptomConfigCollection();

        foreach ($symptomConfigs as $symptomConfig) {
            if ($this->checkSymptom($symptomConfig, $player, $tags, $action)) {
                $activeSymptoms->add($symptomConfig);
            }
        }

        return $activeSymptoms;
    }

    private function checkActivationRequirement(SymptomActivationRequirement $activationRequirement, Player $player, array $tags, ?Action $action): bool
    {
        switch ($activationRequirement->getActivationRequirementName()) {
            case SymptomActivationRequirementEnum::ACTION_DIRTY_RATE:
                return $this->checkActionDirtyRateActivationRequirement($player, $action);

            case SymptomActivationRequirementEnum::ITEM_IN_ROOM:
                return $this->isItemInRoom($activationRequirement->getActivationRequirement(), $player);

            case SymptomActivationRequirementEnum::RANDOM:
                return $this->randomService->isSuccessful(intval($activationRequirement->getValue()));

            case SymptomActivationRequirementEnum::REASON:
                return in_array($activationRequirement->getActivationRequirement(), $tags);

            case SymptomActivationRequirementEnum::PLAYER_EQUIPMENT:
                return $this->checkPlayerEquipmentActivationRequirement($activationRequirement->getActivationRequirement(), $player);

            case SymptomActivationRequirementEnum::PLAYER_IN_ROOM:
                return $this->checkPlayerInRoomActivationRequirement($activationRequirement->getActivationRequirement(), $player);

            case SymptomActivationRequirementEnum::PLAYER_STATUS:
                return $this->checkPlayerStatusActivationRequirement($activationRequirement->getActivationRequirement(), $player);

            default:
                throw new \LogicException('this symptom activationRequirement is not implemented');
        }
    }

    private function checkSymptom(SymptomConfig $symptomConfig, Player $player, array $tags, ?Action $action): bool
    {
        foreach ($symptomConfig->getSymptomActivationRequirements() as $activationRequirement) {
            if (!$this->checkActivationRequirement($activationRequirement, $player, $tags, $action)) {
                return false;
            }
        }

        return true;
    }

    private function checkActionDirtyRateActivationRequirement(Player $player, ?Action $action): bool
    {
        if ($action === null) {
            throw new \LogicException('Provide an action for ACTION_DIRTY_RATE symptom activationRequirement');
        }

        $actionEvent = new ActionVariableEvent(
            $action,
            ActionVariableEnum::PERCENTAGE_DIRTINESS,
            $action->getGameVariables()->getValueByName(ActionVariableEnum::PERCENTAGE_DIRTINESS),
            $player,
            null
        );

        /** @var ActionVariableEvent $rollEvent */
        $rollEvent = $this->eventService->computeEventModifications($actionEvent, ActionVariableEvent::ROLL_ACTION_PERCENTAGE);

        return $this->randomService->isSuccessful($rollEvent->getQuantity());
    }

    private function checkPlayerEquipmentActivationRequirement(?string $expectedEquipment, Player $player): bool
    {
        if ($expectedEquipment === null) {
            throw new \LogicException('Provide an equipment for PLAYER_EQUIPMENT symptom activationRequirement');
        }

        return $player->hasEquipmentByName($expectedEquipment);
    }

    private function checkPlayerInRoomActivationRequirement(?string $playerInRoomActivationRequirement, Player $player): bool
    {
        if ($playerInRoomActivationRequirement === null) {
            throw new \LogicException('Provide a activationRequirement on players for PLAYER_IN_ROOM symptom activationRequirement');
        }

        $playersInRoom = $player->getPlace()->getPlayers()->getPlayerAlive();

        switch ($playerInRoomActivationRequirement) {
            case SymptomActivationRequirementEnum::MUSH_IN_ROOM:
                foreach ($playersInRoom as $playerInRoom) {
                    if ($playerInRoom->isMush()) {
                        return true;
                    }
                }

                return false;

            case SymptomActivationRequirementEnum::NOT_ALONE:
                return count($playersInRoom) > 1;

            default:
                throw new \Exception('Unknown PLAYER_IN_ROOM activationRequirement');
        }
    }

    private function checkPlayerStatusActivationRequirement(?string $expectedStatus, Player $player): bool
    {
        if ($expectedStatus === null) {
            throw new \LogicException('Provide a status for PLAYER_STATUS symptom activationRequirement');
        }

        return $player->hasStatus($expectedStatus);
    }

    private function isItemInRoom(?string $item, Player $player): bool
    {
        if ($item === null) {
            throw new \LogicException('Provide an item name for ITEM_IN_ROOM symptom activationRequirement');
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
