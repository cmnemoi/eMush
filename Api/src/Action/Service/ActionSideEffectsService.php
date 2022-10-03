<?php

namespace Mush\Action\Service;

use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionSideEffectEventEnum;
use Mush\Action\Event\EnhancePercentageRollEvent;
use Mush\Action\Event\PercentageRollEvent;
use Mush\Action\Event\PreparePercentageRollEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Enum\ModifierScopeEnum;
use Mush\Modifier\Service\ModifierServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Service\RoomLogService;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;
use Mush\Game\Service\EventServiceInterface;

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
            $dirtyEvent = new PreparePercentageRollEvent(
                $player,
                $baseDirtyRate,
                $action->getName(),
                $date
            );
            $this->eventService->callEvent($dirtyEvent, PercentageRollEvent::DIRTY_ROLL_RATE);

            $rate = $dirtyEvent->getRate();
            $threshold = $this->randomService->getSuccessThreshold();
            if ($rate < $threshold) {
                return;
            }

            $checkSuccessEvent = new EnhancePercentageRollEvent(
                $player,
                $rate,
                $threshold,
                false,
                $action->getName(),
                $date
            );

            $this->eventService->callEvent($checkSuccessEvent, EnhancePercentageRollEvent::DIRTY_ROLL_RATE, $dirtyEvent);

            if ($checkSuccessEvent->getRate() < $checkSuccessEvent->getThresholdRate()) {
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

        $clumsinessEvent = new PreparePercentageRollEvent(
            $player,
            $baseClumsinessRate,
            $action->getName(),
            $date
        );
        $this->eventService->callEvent($clumsinessEvent, PercentageRollEvent::CLUMSINESS_ROLL_RATE);

        $isHurt = $this->randomService->isSuccessful($clumsinessEvent->getRate());

        if ($isHurt) {
            $this->dispatchPlayerInjuryEvent($player, $date);
        }
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
