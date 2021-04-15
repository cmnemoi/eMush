<?php

namespace Mush\Action\Service;

use Mush\Action\Entity\Action;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Modifier;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\ModifierScopeEnum;
use Mush\Player\Enum\ModifierTargetEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Service\ActionModifierServiceInterface;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ActionSideEffectsService implements ActionSideEffectsServiceInterface
{
    const ACTION_INJURY_MODIFIER = -2;

    private EventDispatcherInterface $eventDispatcher;
    private RandomServiceInterface $randomService;
    private StatusServiceInterface $statusService;
    private RoomLogServiceInterface $roomLogService;
    private ActionModifierServiceInterface $actionModifierService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        RandomServiceInterface $randomService,
        StatusServiceInterface $statusService,
        RoomLogServiceInterface $roomLogService,
        ActionModifierServiceInterface $actionModifierService
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->randomService = $randomService;
        $this->statusService = $statusService;
        $this->roomLogService = $roomLogService;
        $this->actionModifierService = $actionModifierService;
    }

    public function handleActionSideEffect(Action $action, Player $player, ?\DateTime $date = null): Player
    {
        $dirtyRate = $action->getDirtyRate();
        $isSuperDirty = $dirtyRate > 100;
        if (!$player->hasStatus(PlayerStatusEnum::DIRTY) &&
            $dirtyRate > 0 &&
            ($percent = $this->randomService->randomPercent()) <= $dirtyRate
        ) {
            $dirtyRate = $this->actionModifierService->getModifiedValue(
                $dirtyRate,
                $player,
                [ModifierScopeEnum::EVENT_DIRTY],
                ModifierTargetEnum::PERCENTAGE
            );

            if (!$isSuperDirty && $percent >= $dirtyRate) {
                $this->roomLogService->createLog(
                    LogEnum::SOIL_PREVENTED,
                    $player->getPlace(),
                    VisibilityEnum::PRIVATE,
                    'event_log',
                    $player,
                    null,
                    null,
                    $date
                );
            } else {
                $this->statusService->createCoreStatus(PlayerStatusEnum::DIRTY, $player);

                $this->roomLogService->createLog(
                    LogEnum::SOILED,
                    $player->getPlace(),
                    VisibilityEnum::PRIVATE,
                    'event_log',
                    $player,
                    null,
                    null,
                    $date
                );
            }
        }

        $baseInjuryRate = $action->getInjuryRate();
        $baseInjuryRate = 100;
        $injuryRate = $this->actionModifierService->getModifiedValue(
            $baseInjuryRate,
            $player,
            [ModifierScopeEnum::EVENT_CLUMSINESS],
            ModifierTargetEnum::PERCENTAGE
        );

        if ($injuryRate > 0 &&
            ($this->randomService->randomPercent()) <= $injuryRate
        ) {
            if ($baseInjuryRate > $injuryRate) {
                $this->roomLogService->createLog(
                    LogEnum::CLUMSINESS_PREVENTED,
                    $player->getPlace(),
                    VisibilityEnum::PRIVATE,
                    'event_log',
                    $player,
                    null,
                    null,
                    $date
                );
            } else {
                $this->roomLogService->createLog(
                    LogEnum::CLUMSINESS,
                    $player->getPlace(),
                    VisibilityEnum::PRIVATE,
                    'event_log',
                    $player,
                    null,
                    null,
                    $date
                );
                $this->dispatchPlayerInjuryEvent($player, $date);
            }
        }

        return $player;
    }

    private function dispatchPlayerInjuryEvent(Player $player, ?\DateTime $dateTime = null): void
    {
        $modifier = new Modifier();
        $modifier
            ->setDelta(self::ACTION_INJURY_MODIFIER)
            ->setTarget(ModifierTargetEnum::HEALTH_POINT)
        ;

        $playerEvent = new PlayerEvent($player, $dateTime);
        $playerEvent->setModifier($modifier);
        $playerEvent->setReason(EndCauseEnum::CLUMSINESS);
        $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::MODIFIER_PLAYER);
    }
}
