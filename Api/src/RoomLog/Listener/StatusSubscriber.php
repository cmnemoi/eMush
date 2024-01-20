<?php

namespace Mush\RoomLog\Listener;

use Mush\Equipment\Entity\Door;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\StatusEventLogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Event\ChargeStatusEvent;
use Mush\Status\Event\StatusEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StatusSubscriber implements EventSubscriberInterface
{
    private LoggerInterface $logger;
    private RoomLogServiceInterface $roomLogService;

    public function __construct(
        LoggerInterface $logger,
        RoomLogServiceInterface $roomLogService
    ) {
        $this->logger = $logger;
        $this->roomLogService = $roomLogService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StatusEvent::STATUS_APPLIED => 'onStatusApplied',
            StatusEvent::STATUS_REMOVED => 'onStatusRemoved',
            VariableEventInterface::CHANGE_VARIABLE => 'onChangeVariable',
        ];
    }

    public function onStatusApplied(StatusEvent $event): void
    {
        $holder = $event->getStatusHolder();
        $statusName = $event->getStatusName();
        $place = $event->getPlace();

        $logMap = StatusEventLogEnum::STATUS_EVENT_LOGS[StatusEvent::STATUS_APPLIED];
        if (isset($logMap[$statusName])) {
            $logKey = $logMap[$statusName];
        } else {
            return;
        }

        $this->createEventLog($logKey, $event);

        if (
            $holder instanceof Door
            && $statusName === EquipmentStatusEnum::BROKEN
            && $place !== null
        ) {
            $this->roomLogService->createLog(
                $logKey,
                $holder->getOtherRoom($place),
                $event->getVisibility(),
                'event_log',
                null,
                $event->getLogParameters(),
                $event->getTime()
            );
        }
    }

    public function onStatusRemoved(StatusEvent $event): void
    {
        $statusName = $event->getStatusName();
        $logMap = StatusEventLogEnum::STATUS_EVENT_LOGS[StatusEvent::STATUS_REMOVED];

        if (isset($logMap[$statusName])) {
            $logKey = $logMap[$statusName];
        } else {
            return;
        }

        $this->createEventLog($logKey, $event);
    }

    public function onChangeVariable(VariableEventInterface $event): void
    {
        if (!$event instanceof ChargeStatusEvent) {
            return;
        }

        $delta = $event->getRoundedQuantity();
        if ($delta === 0) {
            return;
        }

        // add special logs
        $specialLogMap = StatusEventLogEnum::STATUS_EVENT_LOGS[ChargeStatusEvent::STATUS_CHARGE_UPDATED];
        $specialLogKey = $event->mapLog($specialLogMap[StatusEventLogEnum::VALUE]);

        if ($specialLogKey !== null) {
            $logVisibility = $event->mapLog($specialLogMap[StatusEventLogEnum::VISIBILITY]);

            $this->createEventLog($specialLogKey, $event, $logVisibility ?: VisibilityEnum::HIDDEN);
        }
    }

    private function createEventLog(string $logKey, StatusEvent $event, ?string $visibility = null): void
    {
        $player = $event->getStatusHolder();
        $place = $event->getPlace();

        if ($place === null) {
            return;
        }

        if (!$player instanceof Player) {
            $player = null;
        }

        $this->roomLogService->createLog(
            $logKey,
            $place,
            $visibility ?: $event->getVisibility(),
            'event_log',
            $player,
            $event->getLogParameters(),
            $event->getTime()
        );
    }
}
