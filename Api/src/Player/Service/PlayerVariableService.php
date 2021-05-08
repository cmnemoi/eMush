<?php

namespace Mush\Player\Service;

use Error;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\ModifierScopeEnum;
use Mush\Player\Enum\ModifierTargetEnum;

class PlayerVariableService implements PlayerVariableServiceInterface
{
    private ActionModifierServiceInterface $actionModifierService;

    public function __construct(
        ActionModifierServiceInterface $actionModifierService
    ) {
        $this->actionModifierService = $actionModifierService;
    }

    public function getMaxPlayerVariable(Player $player, string $target): int
    {
        $gameConfig = $player->getDaedalus()->getGameConfig();

        switch ($target) {
            case ModifierTargetEnum::MAX_ACTION_POINT:
                $maxValue = $gameConfig->getMaxActionPoint();
                break;
            case ModifierTargetEnum::MAX_MOVEMENT_POINT:
                $maxValue = $gameConfig->getMaxMovementPoint();
                break;
            case ModifierTargetEnum::MAX_HEALTH_POINT:
                $maxValue = $gameConfig->getMaxHealthPoint();
                break;
            case ModifierTargetEnum::MAX_MORAL_POINT:
                $maxValue = $gameConfig->getMaxMoralPoint();
                break;
            default:
                throw new Error('getMaxPlayerVariable : invalid target string');
        }

        return $this->actionModifierService->getModifiedValue($maxValue, $player, [ModifierScopeEnum::PERMANENT], $target);
    }

    public function setPlayerVariableToMax(Player $player, string $target, \DateTime $date = null): Player
    {
        switch ($target) {
            case ModifierTargetEnum::ACTION_POINT:
                $maxPoint = $this->getMaxPlayerVariable($player, ModifierTargetEnum::MAX_ACTION_POINT);
                $delta = $maxPoint - $player->getActionPoint();
                $player = $this->handleActionPointModifier($delta, $player);
                break;
            case ModifierTargetEnum::MOVEMENT_POINT:
                $maxPoint = $this->getMaxPlayerVariable($player, ModifierTargetEnum::MAX_MOVEMENT_POINT);
                $delta = $maxPoint - $player->getMovementPoint();
                $player = $this->handleMovementPointModifier($delta, $player);
                break;
            case ModifierTargetEnum::HEALTH_POINT:
                $maxPoint = $this->getMaxPlayerVariable($player, ModifierTargetEnum::MAX_HEALTH_POINT);
                $delta = $maxPoint - $player->getHealthPoint();
                $player = $this->handleHealthPointModifier($delta, $player);
                break;
            case ModifierTargetEnum::MORAL_POINT:
                $maxPoint = $this->getMaxPlayerVariable($player, ModifierTargetEnum::MAX_MORAL_POINT);
                $delta = $maxPoint - $player->getMoralPoint();
                $player = $this->handleMoralPointModifier($delta, $player);
                break;
            default:
                throw new Error('getMaxPlayerVariable : invalid target string');
        }

        return $player;
    }

    public function handleActionPointModifier(int $delta, Player $player): Player
    {
        if ($delta !== 0) {
            $playerNewActionPoint = $player->getActionPoint() + $delta;
            $playerMaxActionPoint = $this->getMaxPlayerVariable($player, ModifierTargetEnum::MAX_ACTION_POINT);
            $playerNewActionPoint = $this->getValueInInterval($playerNewActionPoint, 0, $playerMaxActionPoint);
            $player->setActionPoint($playerNewActionPoint);
        }

        return $player;
    }

    public function handleMovementPointModifier(int $delta, Player $player): Player
    {
        if ($delta !== 0) {
            $playerNewMovementPoint = $player->getMovementPoint() + $delta;
            $playerMaxMovementPoint = $this->getMaxPlayerVariable($player, ModifierTargetEnum::MAX_MOVEMENT_POINT);
            $playerNewMovementPoint = $this->getValueInInterval($playerNewMovementPoint, 0, $playerMaxMovementPoint);
            $player->setMovementPoint($playerNewMovementPoint);
        }

        return $player;
    }

    public function handleHealthPointModifier(int $delta, Player $player): Player
    {
        if ($delta !== 0) {
            $playerNewHealthPoint = $player->getHealthPoint() + $delta;
            $playerMaxHealthPoint = $this->getMaxPlayerVariable($player, ModifierTargetEnum::MAX_HEALTH_POINT);
            $playerNewHealthPoint = $this->getValueInInterval($playerNewHealthPoint, 0, $playerMaxHealthPoint);
            $player->setHealthPoint($playerNewHealthPoint);
        }

        return $player;
    }

    public function handleMoralPointModifier(int $delta, Player $player): Player
    {
        if ($delta !== 0) {
            if (!$player->isMush()) {
                $playerNewMoralPoint = $player->getMoralPoint() + $delta;
                $playerMaxMoralPoint = $this->getMaxPlayerVariable($player, ModifierTargetEnum::MAX_MORAL_POINT);
                $playerNewMoralPoint = $this->getValueInInterval($playerNewMoralPoint, 0, $playerMaxMoralPoint);
                $player->setMoralPoint($playerNewMoralPoint);
            }
        }

        return $player;
    }

    public function handleSatietyModifier(int $delta, Player $player): Player
    {
        if ($delta !== 0) {
            if ($delta >= 0 &&
                $player->getSatiety() < 0) {
                $player->setSatiety($delta);
            } else {
                $player->setSatiety($player->getSatiety() + $delta);
            }
        }

        return $player;
    }

    private function getValueInInterval(int $value, int $min, int $max): int
    {
        return max($min, min($max, $value));
    }
}
