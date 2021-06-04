<?php

namespace Mush\Status\Service;

use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;

class PlayerStatusService implements PlayerStatusServiceInterface
{
    public const FULL_STOMACH_STATUS_THRESHOLD = 3;
    public const STARVING_STATUS_THRESHOLD = -24;
    public const SUICIDAL_THRESHOLD = 1;
    public const DEMORALIZED_THRESHOLD = 3;

    private StatusServiceInterface $statusService;

    private RoomLogServiceInterface $roomLogService;

    public function __construct(StatusServiceInterface $statusService, RoomLogServiceInterface $roomLogService)
    {
        $this->statusService = $statusService;
        $this->roomLogService = $roomLogService;
    }

    public function handleSatietyStatus(Player $player, \DateTime $dateTime): void
    {
        $this->handleFullBellyStatus($player);
        $this->handleHungerStatus($player, $dateTime);
    }

    private function handleFullBellyStatus(Player $player): void
    {
        $fullStatus = $player->getStatusByName(PlayerStatusEnum::FULL_STOMACH);
        if ($player->getSatiety() >= self::FULL_STOMACH_STATUS_THRESHOLD && !$fullStatus) {
            $this->statusService->createCoreStatus(PlayerStatusEnum::FULL_STOMACH, $player, null, VisibilityEnum::PRIVATE);
        } elseif ($player->getSatiety() < self::FULL_STOMACH_STATUS_THRESHOLD && $fullStatus) {
            $player->removeStatus($fullStatus);
        }
    }

    private function handleHungerStatus(Player $player, \DateTime $dateTime): void
    {
        $starvingStatus = $player->getStatusByName(PlayerStatusEnum::STARVING);

        if ($player->getSatiety() < self::STARVING_STATUS_THRESHOLD && !$starvingStatus && !$player->isMush()) {
            $this->statusService->createCoreStatus(PlayerStatusEnum::STARVING, $player);

            $this->roomLogService->createLog(
                LogEnum::HUNGER,
                $player->getPlace(),
                VisibilityEnum::PRIVATE,
                'event_log',
                $player,
                null,
                null,
                $dateTime
            );
        } elseif (($player->getSatiety() >= self::STARVING_STATUS_THRESHOLD || $player->isMush()) && $starvingStatus) {
            $player->removeStatus($starvingStatus);
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
}
