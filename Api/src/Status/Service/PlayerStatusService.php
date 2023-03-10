<?php

namespace Mush\Status\Service;

use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;

class PlayerStatusService implements PlayerStatusServiceInterface
{
    public const FULL_STOMACH_STATUS_THRESHOLD = 3;
    public const STARVING_STATUS_THRESHOLD = -24;
    public const SUICIDAL_THRESHOLD = 1;
    public const DEMORALIZED_THRESHOLD = 3;

    private EventServiceInterface $eventService;

    public function __construct(
        EventServiceInterface $eventService,
    ) {
        $this->eventService = $eventService;
    }

    public function handleSatietyStatus(Player $player, \DateTime $dateTime): void
    {
        $this->handleFullBellyStatus($player, $dateTime);
        $this->handleHungerStatus($player, $dateTime);
    }

    private function handleFullBellyStatus(Player $player, \DateTime $dateTime): void
    {
        $event = new StatusEvent(
            PlayerStatusEnum::FULL_STOMACH,
            $player,
            [EventEnum::NEW_CYCLE],
            $dateTime
        );
        $fullStatus = $player->getStatusByName(PlayerStatusEnum::FULL_STOMACH);

        if ($player->getSatiety() >= self::FULL_STOMACH_STATUS_THRESHOLD && !$fullStatus) {
            $this->eventService->callEvent($event, StatusEvent::STATUS_APPLIED);
        } elseif ($player->getSatiety() < self::FULL_STOMACH_STATUS_THRESHOLD && $fullStatus) {
            $this->eventService->callEvent($event, StatusEvent::STATUS_REMOVED);
        }
    }

    private function handleHungerStatus(Player $player, \DateTime $dateTime): void
    {
        $event = new StatusEvent(
            PlayerStatusEnum::STARVING,
            $player,
            [EventEnum::NEW_CYCLE],
            $dateTime
        );
        $starvingStatus = $player->getStatusByName(PlayerStatusEnum::STARVING);

        if ($player->getSatiety() < self::STARVING_STATUS_THRESHOLD && !$starvingStatus && !$player->isMush()) {
            $event->setVisibility(VisibilityEnum::PRIVATE);
            $this->eventService->callEvent($event, StatusEvent::STATUS_APPLIED);
        } elseif (($player->getSatiety() >= self::STARVING_STATUS_THRESHOLD || $player->isMush()) && $starvingStatus) {
            $event->setVisibility(VisibilityEnum::PRIVATE);
            $this->eventService->callEvent($event, StatusEvent::STATUS_REMOVED);
        }
    }

    public function handleMoralStatus(Player $player, \DateTime $dateTime): void
    {
        $demoralizedStatus = $player->getStatusByName(PlayerStatusEnum::DEMORALIZED);
        $suicidalStatus = $player->getStatusByName(PlayerStatusEnum::SUICIDAL);

        $playerMoralPoint = $player->getMoralPoint();

        if ($this->isPlayerSuicidal($playerMoralPoint) && !$suicidalStatus) {
            $event = new StatusEvent(PlayerStatusEnum::SUICIDAL, $player, [EventEnum::NEW_CYCLE], $dateTime);
            $this->eventService->callEvent($event, StatusEvent::STATUS_APPLIED);
        }

        if ($suicidalStatus && !$this->isPlayerSuicidal($playerMoralPoint)) {
            $event = new StatusEvent(PlayerStatusEnum::SUICIDAL, $player, [EventEnum::NEW_CYCLE], $dateTime);
            $this->eventService->callEvent($event, StatusEvent::STATUS_REMOVED);
        }

        if (!$demoralizedStatus && $this->isPlayerDemoralized($playerMoralPoint)) {
            $event = new StatusEvent(PlayerStatusEnum::DEMORALIZED, $player, [EventEnum::NEW_CYCLE], $dateTime);
            $this->eventService->callEvent($event, StatusEvent::STATUS_APPLIED);
        }

        if ($demoralizedStatus && !$this->isPlayerDemoralized($playerMoralPoint)) {
            $event = new StatusEvent(PlayerStatusEnum::DEMORALIZED, $player, [EventEnum::NEW_CYCLE], $dateTime);
            $this->eventService->callEvent($event, StatusEvent::STATUS_REMOVED);
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
