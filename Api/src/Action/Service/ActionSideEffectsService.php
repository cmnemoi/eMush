<?php

namespace Mush\Action\Service;

use Mush\Action\Entity\Action;
use Mush\Action\Event\EnhancePercentageRollEvent;
use Mush\Action\Event\PreparePercentageRollEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Enum\ModifierScopeEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;

class ActionSideEffectsService implements ActionSideEffectsServiceInterface
{
    public const ACTION_INJURY_MODIFIER = -2;

    private EventServiceInterface $eventService;
    private RandomServiceInterface $randomService;
    private RoomLogServiceInterface $roomLogService;

    public function __construct(
        EventServiceInterface $eventService,
        RandomServiceInterface $randomService,
        RoomLogServiceInterface $roomLogService
    ) {
        $this->eventService = $eventService;
        $this->randomService = $randomService;
        $this->roomLogService = $roomLogService;
    }

    public function handleActionSideEffect(Action $action, Player $player, \DateTime $date): Player
    {
        $this->handleDirty($action, $player, $date);
        $this->handleClumsiness($action, $player, $date);

        return $player;
    }

    private function handleDirty(Action $action, Player $player, \DateTime $date): void
    {
        $baseDirtyRate = $action->getDirtyRate();
        $isSuperDirty = $baseDirtyRate > 100;

        if ($player->hasStatus(PlayerStatusEnum::DIRTY)) {
            return;
        }

        if (!$isSuperDirty) {
            $isDirtyRollSuccessful = $this->isSideEffectRollSuccessful(
                $player,
                $baseDirtyRate,
                $action,
                PreparePercentageRollEvent::DIRTY_ROLL_RATE,
                EnhancePercentageRollEvent::DIRTY_ROLL_RATE
            );

            if (!$isDirtyRollSuccessful) {
                return;
            }
        }

        $statusEvent = new StatusEvent(
            PlayerStatusEnum::DIRTY,
            $player,
            $action->getName(),
            new \DateTime()
        );

        $statusEvent->setVisibility(VisibilityEnum::PRIVATE);
        $this->eventService->callEvent($statusEvent, StatusEvent::STATUS_APPLIED);
    }

    private function handleClumsiness(Action $action, Player $player, \DateTime $date): void
    {
        $baseClumsinessRate = $action->getClumsinessRate();

        $isClumsinessRollSuccessful = $this->isSideEffectRollSuccessful(
            $player,
            $baseClumsinessRate,
            $action,
            PreparePercentageRollEvent::CLUMSINESS_ROLL_RATE,
            EnhancePercentageRollEvent::CLUMSINESS_ROLL_RATE
        );

        if ($isClumsinessRollSuccessful) {
            $this->dispatchPlayerInjuryEvent($player, $date);
        }
    }

    private function isSideEffectRollSuccessful(
        Player $player,
        int $baseRate,
        Action $action,
        string $prepareType,
        string $enhanceType
    ): bool {
        $date = new \DateTime();

        $prepareEvent = new PreparePercentageRollEvent(
            $player,
            $baseRate,
            $action->getName(),
            $date
        );
        $this->eventService->callEvent($prepareEvent, $prepareType);

        $rate = $prepareEvent->getRate();
        $threshold = $this->randomService->getSuccessThreshold();
        if ($rate <= $threshold) {
            return false;
        }

        $checkSuccessEvent = new EnhancePercentageRollEvent(
            $player,
            $rate,
            $threshold,
            false,
            $action->getName(),
            $date
        );

        $this->eventService->callEvent($checkSuccessEvent, $enhanceType, $prepareEvent);

        if ($checkSuccessEvent->getRate() <= $checkSuccessEvent->getThresholdRate()) {
            return false;
        }

        return true;
    }

    private function dispatchPlayerInjuryEvent(Player $player, \DateTime $dateTime): void
    {
        $playerModifierEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::HEALTH_POINT,
            self::ACTION_INJURY_MODIFIER,
            ModifierScopeEnum::EVENT_CLUMSINESS,
            $dateTime
        );
        $this->eventService->callEvent($playerModifierEvent, AbstractQuantityEvent::CHANGE_VARIABLE);
    }
}
