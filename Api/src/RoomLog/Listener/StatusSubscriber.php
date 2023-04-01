<?php

namespace Mush\RoomLog\Listener;

use Mush\Equipment\Entity\Door;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\StatusEventLogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
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
        ];
    }

    public function onStatusApplied(StatusEvent $event): void
    {
        $holder = $event->getStatusHolder();
        $statusName = $event->getStatusName();

        $logMap = StatusEventLogEnum::STATUS_EVENT_LOGS[StatusEvent::STATUS_APPLIED];
        if (isset($logMap[$statusName])) {
            $logKey = $logMap[$statusName];
        } else {
            return;
        }

        $this->createEventLog($logKey, $event);

        if ($holder instanceof Door && $statusName === EquipmentStatusEnum::BROKEN) {
            $this->roomLogService->createLog(
                $logKey,
                $holder->getOtherRoom($event->getPlace()),
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

    private function createEventLog(string $logKey, StatusEvent $event): void
    {
        $player = $event->getStatusHolder();
        if (!$player instanceof Player) {
            $player = null;
        }

        $this->roomLogService->createLog(
            $logKey,
            $event->getPlace(),
            $event->getVisibility(),
            'event_log',
            $player,
            $event->getLogParameters(),
            $event->getTime()
        );
    }
}
