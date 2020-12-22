<?php

namespace Mush\Player\Service;

use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Player\Entity\ActionModifier;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

class ActionModifierService implements ActionModifierServiceInterface
{
    private StatusServiceInterface $statusService;
    private RoomLogServiceInterface $roomLogService;
    private GameConfigServiceInterface $gameConfigService;

    public function __construct(StatusServiceInterface $statusService, RoomLogServiceInterface $roomLogService, GameConfigServiceInterface $gameConfigService)
    {
        $this->statusService = $statusService;
        $this->roomLogService = $roomLogService;
        $this->gameConfigService = $gameConfigService;
    }

    public function handlePlayerModifier(Player $player, ActionModifier $actionModifier, \DateTime $date = null): Player
    {
        $date = $date ?? new \DateTime('now');
        $player = $this->handleActionPointModifier($actionModifier, $player, $date);
        $player = $this->handleMovementPointModifier($actionModifier, $player, $date);
        $player = $this->handleHealthPointModifier($actionModifier, $player, $date);
        $player = $this->handleMoralPointModifier($actionModifier, $player, $date);
        $player = $this->handleSatietyModifier($actionModifier, $player);

        return $player;
    }

    private function handleActionPointModifier(ActionModifier $actionModifier, Player $player, \DateTime $date): Player
    {
        $gameConfig = $this->gameConfigService->getConfig();

        if ($actionModifier->getActionPointModifier() !== 0) {
            $playerNewActionPoint = $player->getActionPoint() + $actionModifier->getActionPointModifier();
            $playerNewActionPoint = $this->getValueInInterval($playerNewActionPoint, 0, $gameConfig->getMaxActionPoint());
            $player->setActionPoint($playerNewActionPoint);
            $this->roomLogService->createQuantityLog(
                $actionModifier->getActionPointModifier() > 0 ? LogEnum::GAIN_ACTION_POINT : LogEnum::LOSS_ACTION_POINT,
                $player->getRoom(),
                $player,
                VisibilityEnum::PRIVATE,
                $actionModifier->getActionPointModifier(),
                $date
            );
        }

        return $player;
    }

    private function handleMovementPointModifier(ActionModifier $actionModifier, Player $player, \DateTime $date): Player
    {
        $gameConfig = $this->gameConfigService->getConfig();

        if ($actionModifier->getMovementPointModifier()) {
            $playerNewMovementPoint = $player->getMovementPoint() + $actionModifier->getMovementPointModifier();
            $playerNewMovementPoint = $this->getValueInInterval($playerNewMovementPoint, 0, $gameConfig->getMaxMovementPoint());
            $player->setMovementPoint($playerNewMovementPoint);
            $this->roomLogService->createQuantityLog(
                $actionModifier->getMovementPointModifier() > 0 ? LogEnum::GAIN_MOVEMENT_POINT : LogEnum::LOSS_MOVEMENT_POINT,
                $player->getRoom(),
                $player,
                VisibilityEnum::PRIVATE,
                $actionModifier->getMovementPointModifier(),
                $date
            );
        }

        return $player;
    }

    private function handleHealthPointModifier(ActionModifier $actionModifier, Player $player, \DateTime $date): Player
    {
        $gameConfig = $this->gameConfigService->getConfig();

        if ($actionModifier->getHealthPointModifier()) {
            $playerNewHealthPoint = $player->getHealthPoint() + $actionModifier->getHealthPointModifier();
            $playerNewHealthPoint = $this->getValueInInterval($playerNewHealthPoint, 0, $gameConfig->getMaxHealthPoint());
            $player->setHealthPoint($playerNewHealthPoint);
            $this->roomLogService->createQuantityLog(
                $actionModifier->getHealthPointModifier() > 0 ? LogEnum::GAIN_HEALTH_POINT : LogEnum::LOSS_HEALTH_POINT,
                $player->getRoom(),
                $player,
                VisibilityEnum::PRIVATE,
                $actionModifier->getHealthPointModifier(),
                $date
            );
        }

        return $player;
    }

    private function handleMoralPointModifier(ActionModifier $actionModifier, Player $player, \DateTime $date): Player
    {
        $gameConfig = $this->gameConfigService->getConfig();

        if ($actionModifier->getMoralPointModifier()) {
            if (!$player->isMush()) {
                $playerNewMoralPoint = $player->getMoralPoint() + $actionModifier->getMoralPointModifier();
                $playerNewMoralPoint = $this->getValueInInterval($playerNewMoralPoint, 0, $gameConfig->getMaxMoralPoint());
                $player->setMoralPoint($playerNewMoralPoint);

                $player = $this->handleMoralStatus($player);

                $this->roomLogService->createQuantityLog(
                    $actionModifier->getMoralPointModifier() > 0 ? LogEnum::GAIN_MORAL_POINT : LogEnum::LOSS_MORAL_POINT,
                    $player->getRoom(),
                    $player,
                    VisibilityEnum::PRIVATE,
                    $actionModifier->getMoralPointModifier(),
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

    private function handleSatietyModifier(ActionModifier $actionModifier, Player $player): Player
    {
        if ($actionModifier->getSatietyModifier()) {
            if ($actionModifier->getSatietyModifier() >= 0 &&
                $player->getSatiety() < 0) {
                $player->setSatiety($actionModifier->getSatietyModifier());
            } else {
                $player->setSatiety($player->getSatiety() + $actionModifier->getSatietyModifier());
            }

            $player = $this->handleSatietyStatus($actionModifier, $player);
        }

        return $player;
    }

    private function handleSatietyStatus(ActionModifier $actionModifier, Player $player): Player
    {
        $starvingStatus = $player->getStatusByName(PlayerStatusEnum::STARVING);
        $fullStatus = $player->getStatusByName(PlayerStatusEnum::FULL_STOMACH);

        if (!$player->isMush()) {
            if ($player->getSatiety() < -24 && !$starvingStatus) {
                $this->statusService->createCorePlayerStatus(PlayerStatusEnum::STARVING, $player);
            } elseif ($starvingStatus) {
                $player->removeStatus($starvingStatus);
            }

            if ($player->getSatiety() > 3 && !$fullStatus) {
                $this->statusService->createCorePlayerStatus(PlayerStatusEnum::FULL_STOMACH, $player);
            } elseif ($fullStatus) {
                $player->removeStatus($fullStatus);
            }
        } elseif ($actionModifier->getSatietyModifier() >= 0) {
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

    private function getValueInInterval(int $value, int $min, int $max): int
    {
        return max($min, min($max, $value));
    }
}
