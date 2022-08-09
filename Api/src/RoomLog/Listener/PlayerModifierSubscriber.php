<?php

namespace Mush\RoomLog\Listener;

use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Enum\PlayerModifierLogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerModifierSubscriber implements EventSubscriberInterface
{
    private RoomLogServiceInterface $roomLogService;

    public function __construct(
        RoomLogServiceInterface $roomLogService
    ) {
        $this->roomLogService = $roomLogService;
    }

    public static function getSubscribedEvents()
    {
        return [
            AbstractQuantityEvent::CHANGE_VARIABLE => 'onChangeVariable',
        ];
    }

    public function onChangeVariable(AbstractQuantityEvent $playerEvent): void
    {
        if (!$playerEvent instanceof PlayerVariableEvent) {
            return;
        }

        $delta = $playerEvent->getQuantity();
        $modifiedVariable = $playerEvent->getModifiedVariable();

        if ($delta === 0) {
            return;
        }

        // add special logs
        $logMap = PlayerModifierLogEnum::PLAYER_VARIABLE_SPECIAL_LOGS;
        $reason = $playerEvent->getReason();
        if (isset($logMap[$reason])) {
            $logKey = $logMap[$reason][PlayerModifierLogEnum::VALUE];
            $logVisibility = $logMap[$reason][PlayerModifierLogEnum::VISIBILITY];
            $this->createEventLog($logKey, $playerEvent, $logVisibility);
        }

        $gainOrLoss = $delta > 0 ? PlayerModifierLogEnum::GAIN : PlayerModifierLogEnum::LOSS;
        $logMap = PlayerModifierLogEnum::PLAYER_VARIABLE_LOGS[$gainOrLoss];
        if (isset($logMap[$modifiedVariable])) {
            $logKey = $logMap[$modifiedVariable];
            $this->createEventLog($logKey, $playerEvent, $playerEvent->getVisibility());
        }
    }

    private function createEventLog(string $logKey, PlayerEvent $event, string $logVisibility): void
    {
        $player = $event->getPlayer();

        $this->roomLogService->createLog(
            $logKey,
            $event->getPlace(),
            $logVisibility,
            'event_log',
            $player,
            $event->getLogParameters(),
            $event->getTime()
        );
    }
}
