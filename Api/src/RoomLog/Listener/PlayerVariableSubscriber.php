<?php

namespace Mush\RoomLog\Listener;

use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Enum\PlayerModifierLogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerVariableSubscriber implements EventSubscriberInterface
{
    private RoomLogServiceInterface $roomLogService;

    public function __construct(RoomLogServiceInterface $roomLogService)
    {
        $this->roomLogService = $roomLogService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            VariableEventInterface::CHANGE_VARIABLE => 'onChangeVariable',
        ];
    }

    public function onChangeVariable(VariableEventInterface $playerEvent): void
    {
        if (!$playerEvent instanceof PlayerVariableEvent) {
            return;
        }

        $delta = $playerEvent->getRoundedQuantity();
        $modifiedVariable = $playerEvent->getVariableName();

        if ($delta === 0) {
            return;
        }

        // add special logs
        $specialLogMap = PlayerModifierLogEnum::PLAYER_VARIABLE_SPECIAL_LOGS;
        $specialLogKeys = $playerEvent->mapMultipleLogs($specialLogMap[PlayerModifierLogEnum::VALUE]);

        foreach ($specialLogKeys as $specialLogKey) {
            if ($specialLogKey !== null) {
                $logVisibility = $playerEvent->mapLog($specialLogMap[PlayerModifierLogEnum::VISIBILITY]);

                $this->createEventLog($specialLogKey, $playerEvent, $logVisibility ?: VisibilityEnum::HIDDEN);
            }
        }

        $gainOrLoss = $delta > 0 ? PlayerModifierLogEnum::GAIN : PlayerModifierLogEnum::LOSS;
        $logMap = PlayerModifierLogEnum::PLAYER_VARIABLE_LOGS[$gainOrLoss];

        if (\array_key_exists($modifiedVariable, $logMap)) {
            $logKey = $logMap[$modifiedVariable];
            $this->createEventLog($logKey, $playerEvent, $playerEvent->getVisibility());
        }
    }

    private function createEventLog(string $logKey, PlayerEvent $event, string $logVisibility): void
    {
        $this->roomLogService->createLog(
            $logKey,
            $event->getPlace(),
            $logVisibility,
            'event_log',
            $event->getPlayer(),
            $event->getLogParameters(),
            $event->getTime()
        );
    }
}
