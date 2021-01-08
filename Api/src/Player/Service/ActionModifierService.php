<?php

namespace Mush\Player\Service;

use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Player\Entity\Modifier;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\ModifierTargetEnum;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

class ActionModifierService implements ActionModifierServiceInterface
{
    const FULL_STOMACH_STATUS_THRESHOLD = 3;
    const STARVING_STATUS_THRESHOLD = -24;

    private StatusServiceInterface $statusService;
    private RoomLogServiceInterface $roomLogService;
    private GameConfigServiceInterface $gameConfigService;

    public function __construct(StatusServiceInterface $statusService, RoomLogServiceInterface $roomLogService, GameConfigServiceInterface $gameConfigService)
    {
        $this->statusService = $statusService;
        $this->roomLogService = $roomLogService;
        $this->gameConfigService = $gameConfigService;
    }

    public function handlePlayerModifier(Player $player, Modifier $actionModifier, \DateTime $date = null): Player
    {
        $date = $date ?? new \DateTime('now');
        $delta = $actionModifier->getDelta();
        switch ($actionModifier->getTarget()) {
            case ModifierTargetEnum::ACTION_POINT:
                $player = $this->handleActionPointModifier($delta, $player, $date);
                break;
            case ModifierTargetEnum::MOVEMENT_POINT:
                $player = $this->handleMovementPointModifier($delta, $player, $date);
                break;
            case ModifierTargetEnum::HEAL_POINT:
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

    private function handleActionPointModifier(int $actionModifier, Player $player, \DateTime $date): Player
    {
        $gameConfig = $this->gameConfigService->getConfig();

        if ($actionModifier !== 0) {
            $playerNewActionPoint = $player->getActionPoint() + $actionModifier;
            $playerNewActionPoint = $this->getValueInInterval($playerNewActionPoint, 0, $gameConfig->getMaxActionPoint());
            $player->setActionPoint($playerNewActionPoint);
            $this->roomLogService->createQuantityLog(
                $actionModifier > 0 ? LogEnum::GAIN_ACTION_POINT : LogEnum::LOSS_ACTION_POINT,
                $player->getRoom(),
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
        $gameConfig = $this->gameConfigService->getConfig();

        if ($movementModifier !== 0) {
            $playerNewMovementPoint = $player->getMovementPoint() + $movementModifier;
            $playerNewMovementPoint = $this->getValueInInterval($playerNewMovementPoint, 0, $gameConfig->getMaxMovementPoint());
            $player->setMovementPoint($playerNewMovementPoint);
            $this->roomLogService->createQuantityLog(
                $movementModifier > 0 ? LogEnum::GAIN_MOVEMENT_POINT : LogEnum::LOSS_MOVEMENT_POINT,
                $player->getRoom(),
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
        $gameConfig = $this->gameConfigService->getConfig();

        if ($healthModifier !== 0) {
            $playerNewHealthPoint = $player->getHealthPoint() + $healthModifier;
            $playerNewHealthPoint = $this->getValueInInterval($playerNewHealthPoint, 0, $gameConfig->getMaxHealthPoint());
            $player->setHealthPoint($playerNewHealthPoint);
            $this->roomLogService->createQuantityLog(
                $healthModifier > 0 ? LogEnum::GAIN_HEALTH_POINT : LogEnum::LOSS_HEALTH_POINT,
                $player->getRoom(),
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
        $gameConfig = $this->gameConfigService->getConfig();

        if ($moralModifier !== 0) {
            if (!$player->isMush()) {
                $playerNewMoralPoint = $player->getMoralPoint() + $moralModifier;
                $playerNewMoralPoint = $this->getValueInInterval($playerNewMoralPoint, 0, $gameConfig->getMaxMoralPoint());
                $player->setMoralPoint($playerNewMoralPoint);

                $player = $this->handleMoralStatus($player);

                $this->roomLogService->createQuantityLog(
                    $moralModifier > 0 ? LogEnum::GAIN_MORAL_POINT : LogEnum::LOSS_MORAL_POINT,
                    $player->getRoom(),
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

        if ($player->getMoralPoint() <= 1 && !$suicidalStatus) {
            $this->statusService->createCorePlayerStatus(PlayerStatusEnum::SUICIDAL, $player);
        } elseif ($suicidalStatus) {
            $player->removeStatus($suicidalStatus);
        }

        if ($player->getMoralPoint() <= 4 && $player->getMoralPoint() > 1 && $demoralizedStatus) {
            $this->statusService->createCorePlayerStatus(PlayerStatusEnum::DEMORALIZED, $player);
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
            $this->statusService->createChargePlayerStatus(
                PlayerStatusEnum::FULL_STOMACH,
                $player,
                ChargeStrategyTypeEnum::CYCLE_DECREMENT,
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
            $this->statusService->createCorePlayerStatus(PlayerStatusEnum::STARVING, $player);
        } elseif ($player->getSatiety() >= self::STARVING_STATUS_THRESHOLD && $starvingStatus) {
            $player->removeStatus($starvingStatus);
        }

        if ($player->getSatiety() >= self::FULL_STOMACH_STATUS_THRESHOLD && !$fullStatus) {
            $this->statusService->createCorePlayerStatus(PlayerStatusEnum::FULL_STOMACH, $player);
        } elseif ($fullStatus) {
            $player->removeStatus($fullStatus);
        }

        return $player;
    }

    private function getValueInInterval(int $value, int $min, int $max): int
    {
        return max($min, min($max, $value));
    }
}
