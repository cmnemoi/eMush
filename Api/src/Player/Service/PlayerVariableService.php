<?php

namespace Mush\Player\Service;

use Error;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\ModifierScopeEnum;
use Mush\Player\Enum\ModifierTargetEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

class PlayerVariableService implements PlayerVariableServiceInterface
{
    const FULL_STOMACH_STATUS_THRESHOLD = 4;
    const STARVING_STATUS_THRESHOLD = -24;
    const SUICIDAL_THRESHOLD = 1;
    const DEMORALIZED_THRESHOLD = 3;

    private StatusServiceInterface $statusService;
    private ActionModifierServiceInterface $actionModifierService;

    public function __construct(
        StatusServiceInterface $statusService,
        ActionModifierServiceInterface $actionModifierService
    ) {
        $this->statusService = $statusService;
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
        $maxPoint = $this->getMaxPlayerVariable($player, $target);
        switch ($target) {
            case ModifierTargetEnum::ACTION_POINT:
                $delta = $maxPoint - $player->getActionPoint();
                $player = $this->handleActionPointModifier($delta, $player);
                break;
            case ModifierTargetEnum::MOVEMENT_POINT:
                $delta = $maxPoint - $player->getMovementPoint();
                $player = $this->handleMovementPointModifier($delta, $player);
                break;
            case ModifierTargetEnum::HEALTH_POINT:
                $delta = $maxPoint - $player->getHealthPoint();
                $player = $this->handleHealthPointModifier($delta, $player);
                break;
            case ModifierTargetEnum::MORAL_POINT:
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

                $player = $this->handleMoralStatus($player);
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

            $player = $this->handleSatietyStatus($delta, $player);
        }

        return $player;
    }

    private function handleMoralStatus(Player $player): Player
    {
        $demoralizedStatus = $player->getStatusByName(PlayerStatusEnum::DEMORALIZED);
        $suicidalStatus = $player->getStatusByName(PlayerStatusEnum::SUICIDAL);

        if ($player->getMoralPoint() <= self::SUICIDAL_THRESHOLD && !$suicidalStatus) {
            $this->statusService->createCoreStatus(PlayerStatusEnum::SUICIDAL, $player, null, VisibilityEnum::PRIVATE);
        } elseif ($suicidalStatus) {
            $player->removeStatus($suicidalStatus);
        }

        if ($player->getMoralPoint() <= self::DEMORALIZED_THRESHOLD &&
            $player->getMoralPoint() > self::SUICIDAL_THRESHOLD && !$demoralizedStatus
        ) {
            $this->statusService->createCoreStatus(PlayerStatusEnum::DEMORALIZED, $player, null, VisibilityEnum::PRIVATE);
        } elseif ($demoralizedStatus) {
            $player->removeStatus($demoralizedStatus);
        }

        return $player;
    }

    private function handleSatietyStatus(int $satietyModifier, Player $player): Player
    {
        if (!$player->isMush()) {
            $player = $this->handleHumanStatus($player);
        } elseif ($satietyModifier >= 0) {
            $this->statusService->createChargeStatus(
                PlayerStatusEnum::FULL_STOMACH,
                $player,
                ChargeStrategyTypeEnum::CYCLE_DECREMENT,
                null,
                VisibilityEnum::PRIVATE,
                VisibilityEnum::HIDDEN,
                2,
                0,
                true
            );
        }

        return $player;
    }

    private function handleHumanStatus(Player $player): Player
    {
        $starvingStatus = $player->getStatusByName(PlayerStatusEnum::STARVING);
        $fullStatus = $player->getStatusByName(PlayerStatusEnum::FULL_STOMACH);

        if ($player->getSatiety() < self::STARVING_STATUS_THRESHOLD && !$starvingStatus) {
            $this->statusService->createCoreStatus(PlayerStatusEnum::STARVING, $player);
        } elseif ($player->getSatiety() >= self::STARVING_STATUS_THRESHOLD && $starvingStatus) {
            $player->removeStatus($starvingStatus);
        }

        if ($player->getSatiety() >= self::FULL_STOMACH_STATUS_THRESHOLD && !$fullStatus) {
            $this->statusService->createCoreStatus(PlayerStatusEnum::FULL_STOMACH, $player, null, VisibilityEnum::PRIVATE);
        } elseif ($player->getSatiety() < self::FULL_STOMACH_STATUS_THRESHOLD && $fullStatus) {
            $player->removeStatus($fullStatus);
        }

        return $player;
    }

    private function getValueInInterval(int $value, int $min, int $max): int
    {
        return max($min, min($max, $value));
    }
}
