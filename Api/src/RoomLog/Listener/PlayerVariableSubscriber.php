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
        $specialLogKey = $playerEvent->mapLog($specialLogMap[PlayerModifierLogEnum::VALUE]);

        /* TODO: Check if the player already had the modifier applied.
         * According to https://discord.com/channels/693082011484684348/746873392463872071/1224380512290734100
         * The event modifier applied will apply itself a tag for each subsequent of the chain
         * To ensure no infinite loop are created.
         */

        if ($specialLogKey !== null) {
            $logVisibility = $playerEvent->mapLog($specialLogMap[PlayerModifierLogEnum::VISIBILITY]);

            $this->createEventLog($specialLogKey, $playerEvent, $logVisibility ?: VisibilityEnum::HIDDEN);
        }

        $gainOrLoss = $delta > 0 ? PlayerModifierLogEnum::GAIN : PlayerModifierLogEnum::LOSS;
        $logMap = PlayerModifierLogEnum::PLAYER_VARIABLE_LOGS[$gainOrLoss];

        if (array_key_exists($modifiedVariable, $logMap)) {
            $logKey = $logMap[$modifiedVariable];
            if ($this->ensureEventAreNotDoubleDispatched($playerEvent, $logKey)) {
                return;
            }

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

    private function ensureEventAreNotDoubleDispatched(PlayerEvent $event, string $logKey): bool
    {
        return $event->hasTag($logKey);
    }
}
