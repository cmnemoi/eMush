<?php

namespace Mush\Action\Service;

use Mush\Action\Entity\Action;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\ModifierScopeEnum;
use Mush\Player\Enum\ModifierTargetEnum;
use Mush\Player\Event\PlayerModifierEventInterface;
use Mush\Player\Service\ActionModifierServiceInterface;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEventInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ActionSideEffectsService implements ActionSideEffectsServiceInterface
{
    public const ACTION_INJURY_MODIFIER = -2;

    private EventDispatcherInterface $eventDispatcher;
    private RandomServiceInterface $randomService;
    private RoomLogServiceInterface $roomLogService;
    private ActionModifierServiceInterface $actionModifierService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        RandomServiceInterface $randomService,
        RoomLogServiceInterface $roomLogService,
        ActionModifierServiceInterface $actionModifierService
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->randomService = $randomService;
        $this->roomLogService = $roomLogService;
        $this->actionModifierService = $actionModifierService;
    }

    public function handleActionSideEffect(Action $action, Player $player, \DateTime $date): Player
    {
        $this->handleDirty($action, $player, $date);
        $this->handleInjury($action, $player, $date);

        return $player;
    }

    private function handleDirty(Action $action, Player $player, ?\DateTime $date): void
    {
        $baseDirtyRate = $action->getDirtyRate();
        $isSuperDirty = $baseDirtyRate > 100;
        if (!$player->hasStatus(PlayerStatusEnum::DIRTY) &&
            $baseDirtyRate > 0) {
            $dirtyRate = $this->actionModifierService->getModifiedValue(
                $baseDirtyRate,
                $player,
                [ModifierScopeEnum::EVENT_DIRTY],
                ModifierTargetEnum::PERCENTAGE
            );

            $percent = $this->randomService->randomPercent();

            if ($percent <= $baseDirtyRate && $percent > $dirtyRate && !$isSuperDirty) {
                $this->roomLogService->createLog(
                    LogEnum::SOIL_PREVENTED,
                    $player->getPlace(),
                    VisibilityEnum::PRIVATE,
                    'event_log',
                    $player,
                    [],
                    $date
                );
            } elseif ($percent <= $dirtyRate) {
                $statusEvent = new StatusEventInterface(PlayerStatusEnum::DIRTY, $player, EventEnum::NEW_DAY, new \DateTime());
                $statusEvent->setVisibility(VisibilityEnum::PRIVATE);
                $this->eventDispatcher->dispatch($statusEvent, StatusEventInterface::STATUS_APPLIED);
            }
        }
    }

    private function handleInjury(Action $action, Player $player, \DateTime $date): void
    {
        $baseInjuryRate = $action->getInjuryRate();
        if ($baseInjuryRate > 0) {
            $injuryRate = $this->actionModifierService->getModifiedValue(
                $baseInjuryRate,
                $player,
                [ModifierScopeEnum::EVENT_CLUMSINESS],
                ModifierTargetEnum::PERCENTAGE
            );

            $percent = $this->randomService->randomPercent();

            if ($percent <= $baseInjuryRate && $percent > $injuryRate) {
                $this->roomLogService->createLog(
                    LogEnum::CLUMSINESS_PREVENTED,
                    $player->getPlace(),
                    VisibilityEnum::PRIVATE,
                    'event_log',
                    $player,
                    [],
                    $date
                );
            } elseif ($percent <= $injuryRate) {
                $this->roomLogService->createLog(
                    LogEnum::CLUMSINESS,
                    $player->getPlace(),
                    VisibilityEnum::PRIVATE,
                    'event_log',
                    $player,
                    [],
                    $date
                );
                $this->dispatchPlayerInjuryEvent($player, $date);
            }
        }
    }

    private function dispatchPlayerInjuryEvent(Player $player, \DateTime $dateTime): void
    {
        $playerModifierEvent = new PlayerModifierEventInterface(
            $player,
            self::ACTION_INJURY_MODIFIER,
            EndCauseEnum::CLUMSINESS,
            $dateTime
        );
        $this->eventDispatcher->dispatch($playerModifierEvent, PlayerModifierEventInterface::HEALTH_POINT_MODIFIER);
    }
}
