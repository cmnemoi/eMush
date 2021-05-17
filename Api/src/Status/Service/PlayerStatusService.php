<?php

namespace Mush\Status\Service;

use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\PlayerStatusEnum;

class PlayerStatusService implements PlayerStatusServiceInterface
{
    const FULL_STOMACH_STATUS_THRESHOLD = 3;
    const STARVING_STATUS_THRESHOLD = -24;
    const SUICIDAL_THRESHOLD = 1;
    const DEMORALIZED_THRESHOLD = 3;

    private StatusServiceInterface $statusService;

    private RoomLogServiceInterface $roomLogService;

    public function __construct(StatusServiceInterface $statusService, RoomLogServiceInterface $roomLogService)
    {
        $this->statusService = $statusService;
        $this->roomLogService = $roomLogService;
    }

    public function handleSatietyStatus(int $satietyModifier, Player $player, \DateTime $dateTime): void
    {
        if (!$player->isMush()) {
            $this->handleHumanSatietyStatus($player, $dateTime);
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
    }

    public function handleMoralStatus(Player $player): void
    {
        $demoralizedStatus = $player->getStatusByName(PlayerStatusEnum::DEMORALIZED);
        $suicidalStatus = $player->getStatusByName(PlayerStatusEnum::SUICIDAL);

        $playerMoralPoint = $player->getMoralPoint();

        if ($this->isPlayerSuicidal($playerMoralPoint) && !$suicidalStatus) {
            $this->statusService->createCoreStatus(PlayerStatusEnum::SUICIDAL, $player, null, VisibilityEnum::PRIVATE);
        }

        if ($suicidalStatus && !$this->isPlayerSuicidal($playerMoralPoint)) {
            $player->removeStatus($suicidalStatus);
        }

        if (!$demoralizedStatus && $this->isPlayerDemoralized($playerMoralPoint)) {
            $this->statusService->createCoreStatus(PlayerStatusEnum::DEMORALIZED, $player, null, VisibilityEnum::PRIVATE);
        }

        if ($demoralizedStatus && !$this->isPlayerDemoralized($playerMoralPoint)) {
            $player->removeStatus($demoralizedStatus);
        }
    }

    private function isPlayerSuicidal(int $playerMoralPoint): bool
    {
        return $playerMoralPoint <= self::SUICIDAL_THRESHOLD;
    }

    private function isPlayerDemoralized(int $playerMoralPoint): bool
    {
        return $playerMoralPoint <= self::DEMORALIZED_THRESHOLD && $playerMoralPoint > self::SUICIDAL_THRESHOLD;
    }

    private function handleHumanSatietyStatus(Player $player, \DateTime $dateTime): void
    {
        $starvingStatus = $player->getStatusByName(PlayerStatusEnum::STARVING);
        $fullStatus = $player->getStatusByName(PlayerStatusEnum::FULL_STOMACH);

        if ($player->getSatiety() < self::STARVING_STATUS_THRESHOLD && !$starvingStatus) {
            $this->statusService->createCoreStatus(PlayerStatusEnum::STARVING, $player);

            $this->roomLogService->createLog(
                LogEnum::FORCE_GET_UP,
                $player->getPlace(),
                VisibilityEnum::PRIVATE,
                'event_log',
                $player,
                null,
                null,
                $dateTime
            );
        } elseif ($player->getSatiety() >= self::STARVING_STATUS_THRESHOLD && $starvingStatus) {
            $player->removeStatus($starvingStatus);
        }

        if ($player->getSatiety() >= self::FULL_STOMACH_STATUS_THRESHOLD && !$fullStatus) {
            $this->statusService->createCoreStatus(PlayerStatusEnum::FULL_STOMACH, $player, null, VisibilityEnum::PRIVATE);
        } elseif ($player->getSatiety() < self::FULL_STOMACH_STATUS_THRESHOLD && $fullStatus) {
            $player->removeStatus($fullStatus);
        }
    }
}
