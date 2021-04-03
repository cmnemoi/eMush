<?php

namespace Mush\Player\Service;

use Error;
use Mush\Player\Entity\Modifier;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\ModifierScopeEnum;
use Mush\Player\Enum\ModifierTargetEnum;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
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
    private RoomLogServiceInterface $roomLogService;
    private ActionModifierServiceInterface $actionModifierService;

    public function __construct(
        StatusServiceInterface $statusService,
        RoomLogServiceInterface $roomLogService,
        ActionModifierServiceInterface $actionModifierService
    ) {
        $this->statusService = $statusService;
        $this->roomLogService = $roomLogService;
        $this->actionModifierService = $actionModifierService;
    }

    public function modifyPlayerVariable(Player $player, Modifier $actionModifier, \DateTime $date = null): Player
    {
        $date = $date ?? new \DateTime('now');
        $delta = (int) $actionModifier->getDelta();
        switch ($actionModifier->getTarget()) {
            case ModifierTargetEnum::ACTION_POINT:
                $player = $this->handleActionPointModifier($delta, $player, $date);
                break;
            case ModifierTargetEnum::MOVEMENT_POINT:
                $player = $this->handleMovementPointModifier($delta, $player, $date);
                break;
            case ModifierTargetEnum::HEALTH_POINT:
                $player = $this->handleHealthPointModifier($delta, $player, $date);
                break;
            case ModifierTargetEnum::MORAL_POINT:
                $player = $this->handleMoralPointModifier($delta, $player, $date);
                break;
            case ModifierTargetEnum::SATIETY:
                $player = $this->handleSatietyModifier($delta, $player);
                break;
        }

        return $player;
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

    private function handleActionPointModifier(int $actionModifier, Player $player, \DateTime $date): Player
    {
        if ($actionModifier !== 0) {
            $playerNewActionPoint = $player->getActionPoint() + $actionModifier;
            $playerMaxActionPoint = $this->getMaxPlayerVariable($player, ModifierTargetEnum::MAX_ACTION_POINT);
            $playerNewActionPoint = $this->getValueInInterval($playerNewActionPoint, 0, $playerMaxActionPoint);
            $player->setActionPoint($playerNewActionPoint);
            $this->roomLogService->createQuantityLog(
                $actionModifier > 0 ? LogEnum::GAIN_ACTION_POINT : LogEnum::LOSS_ACTION_POINT,
                $player->getPlace(),
                $player,
                VisibilityEnum::PRIVATE,
                abs($actionModifier),
                $date
            );
        }

        return $player;
    }

    private function handleMovementPointModifier(int $movementModifier, Player $player, \DateTime $date): Player
    {
        if ($movementModifier !== 0) {
            $playerNewMovementPoint = $player->getMovementPoint() + $movementModifier;
            $playerMaxMovementPoint = $this->getMaxPlayerVariable($player, ModifierTargetEnum::MAX_MOVEMENT_POINT);
            $playerNewMovementPoint = $this->getValueInInterval($playerNewMovementPoint, 0, $playerMaxMovementPoint);
            $player->setMovementPoint($playerNewMovementPoint);
            $this->roomLogService->createQuantityLog(
                $movementModifier > 0 ? LogEnum::GAIN_MOVEMENT_POINT : LogEnum::LOSS_MOVEMENT_POINT,
                $player->getPlace(),
                $player,
                VisibilityEnum::PRIVATE,
                abs($movementModifier),
                $date
            );
        }

        return $player;
    }

    private function handleHealthPointModifier(int $healthModifier, Player $player, \DateTime $date): Player
    {
        if ($healthModifier !== 0) {
            $playerNewHealthPoint = $player->getHealthPoint() + $healthModifier;
            $playerMaxHealthPoint = $this->getMaxPlayerVariable($player, ModifierTargetEnum::MAX_HEALTH_POINT);
            $playerNewHealthPoint = $this->getValueInInterval($playerNewHealthPoint, 0, $playerMaxHealthPoint);
            $player->setHealthPoint($playerNewHealthPoint);
            $this->roomLogService->createQuantityLog(
                $healthModifier > 0 ? LogEnum::GAIN_HEALTH_POINT : LogEnum::LOSS_HEALTH_POINT,
                $player->getPlace(),
                $player,
                VisibilityEnum::PRIVATE,
                abs($healthModifier),
                $date
            );
        }

        return $player;
    }

    private function handleMoralPointModifier(int $moralModifier, Player $player, \DateTime $date): Player
    {
        if ($moralModifier !== 0) {
            if (!$player->isMush()) {
                $playerNewMoralPoint = $player->getMoralPoint() + $moralModifier;
                $playerMaxMoralPoint = $this->getMaxPlayerVariable($player, ModifierTargetEnum::MAX_MORAL_POINT);
                $playerNewMoralPoint = $this->getValueInInterval($playerNewMoralPoint, 0, $playerMaxMoralPoint);
                $player->setMoralPoint($playerNewMoralPoint);

                $player = $this->handleMoralStatus($player);

                $this->roomLogService->createQuantityLog(
                    $moralModifier > 0 ? LogEnum::GAIN_MORAL_POINT : LogEnum::LOSS_MORAL_POINT,
                    $player->getPlace(),
                    $player,
                    VisibilityEnum::PRIVATE,
                    abs($moralModifier),
                    $date
                );
            }
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

    private function handleSatietyModifier(int $satietyModifier, Player $player): Player
    {
        if ($satietyModifier !== 0) {
            if ($satietyModifier >= 0 &&
                $player->getSatiety() < 0) {
                $player->setSatiety($satietyModifier);
            } else {
                $player->setSatiety($player->getSatiety() + $satietyModifier);
            }

            $player = $this->handleSatietyStatus($satietyModifier, $player);
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
