<?php

namespace Mush\Status\Service;

use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\PlayerStatusEnum;

class PlayerStatusService implements PlayerStatusServiceInterface
{
    public const FULL_STOMACH_STATUS_THRESHOLD = 3;
    public const STARVING_WARNING_STATUS_THRESHOLD = -23;
    public const STARVING_STATUS_THRESHOLD = -24;
    public const SUICIDAL_THRESHOLD = 1;
    public const DEMORALIZED_THRESHOLD = 3;

    private StatusServiceInterface $statusService;

    public function __construct(
        StatusServiceInterface $statusService,
    ) {
        $this->statusService = $statusService;
    }

    public function handleSatietyStatus(Player $player, \DateTime $dateTime): void
    {
        $this->handleFullBellyStatus($player, $dateTime);
        $this->handleHungerStatus($player, $dateTime);
    }

    public function handleMoralStatus(Player $player, \DateTime $dateTime): void
    {
        $demoralizedStatus = $player->getStatusByName(PlayerStatusEnum::DEMORALIZED);
        $suicidalStatus = $player->getStatusByName(PlayerStatusEnum::SUICIDAL);

        $playerMoralPoint = $player->getMoralPoint();

        if ($this->isPlayerSuicidal($playerMoralPoint) && !$suicidalStatus) {
            $this->statusService->createStatusFromName(PlayerStatusEnum::SUICIDAL, $player, [EventEnum::NEW_CYCLE], $dateTime);
        }

        if ($suicidalStatus && !$this->isPlayerSuicidal($playerMoralPoint)) {
            $this->statusService->removeStatus(PlayerStatusEnum::SUICIDAL, $player, [EventEnum::NEW_CYCLE], $dateTime);
        }

        if (!$demoralizedStatus && $this->isPlayerDemoralized($playerMoralPoint)) {
            $this->statusService->createStatusFromName(PlayerStatusEnum::DEMORALIZED, $player, [EventEnum::NEW_CYCLE], $dateTime);
        }

        if ($demoralizedStatus && !$this->isPlayerDemoralized($playerMoralPoint)) {
            $this->statusService->removeStatus(PlayerStatusEnum::DEMORALIZED, $player, [EventEnum::NEW_CYCLE], $dateTime);
        }
    }

    private function handleFullBellyStatus(Player $player, \DateTime $dateTime): void
    {
        $fullStatus = $player->getStatusByName(PlayerStatusEnum::FULL_STOMACH);

        if ($player->getSatiety() >= self::FULL_STOMACH_STATUS_THRESHOLD && !$fullStatus) {
            $this->statusService->createStatusFromName(
                PlayerStatusEnum::FULL_STOMACH,
                $player,
                [EventEnum::NEW_CYCLE],
                $dateTime
            );
        } elseif ($player->getSatiety() < self::FULL_STOMACH_STATUS_THRESHOLD && $fullStatus) {
            $this->statusService->removeStatus(
                PlayerStatusEnum::FULL_STOMACH,
                $player,
                [EventEnum::NEW_CYCLE],
                $dateTime
            );
        }
    }

    private function handleHungerStatus(Player $player, \DateTime $dateTime): void
    {
        if ($player->isMush()) {
            $this->statusService->removeStatus(
                PlayerStatusEnum::STARVING_WARNING,
                $player,
                [EventEnum::NEW_CYCLE],
                $dateTime,
                VisibilityEnum::PRIVATE
            );
            $this->statusService->removeStatus(
                PlayerStatusEnum::STARVING,
                $player,
                [EventEnum::NEW_CYCLE],
                $dateTime,
                VisibilityEnum::PRIVATE
            );

            return;
        }

        if ($player->getSatiety() < self::STARVING_STATUS_THRESHOLD) {
            $this->statusService->removeStatus(
                PlayerStatusEnum::STARVING_WARNING,
                $player,
                [EventEnum::NEW_CYCLE],
                $dateTime,
                VisibilityEnum::PRIVATE
            );
            $this->statusService->createStatusFromName(
                PlayerStatusEnum::STARVING,
                $player,
                [EventEnum::NEW_CYCLE],
                $dateTime,
                null,
                VisibilityEnum::PRIVATE
            );
        } elseif ($player->getSatiety() < self::STARVING_WARNING_STATUS_THRESHOLD) {
            $this->statusService->createStatusFromName(
                PlayerStatusEnum::STARVING_WARNING,
                $player,
                [EventEnum::NEW_CYCLE],
                $dateTime,
                null,
                VisibilityEnum::PRIVATE
            );
        } elseif ($player->getSatiety() >= self::STARVING_WARNING_STATUS_THRESHOLD) {
            $this->statusService->removeStatus(
                PlayerStatusEnum::STARVING,
                $player,
                [EventEnum::NEW_CYCLE],
                $dateTime,
                VisibilityEnum::PRIVATE
            );
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
