<?php

namespace Mush\RoomLog\Listener;

use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\PlantLogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class StatusSubscriber implements EventSubscriberInterface
{
    private RoomLogServiceInterface $roomLogService;

    public function __construct(
        RoomLogServiceInterface $roomLogService
    ) {
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
        if ($event->getStatusName() === PlayerStatusEnum::STARVING) {
            $holder = $event->getStatusHolder();
            if (!$holder instanceof Player) {
                throw new UnexpectedTypeException($holder, Player::class);
            }

            $this->roomLogService->createLog(
                LogEnum::HUNGER,
                $event->getPlace(),
                $event->getVisibility(),
                'event_log',
                $holder,
                $event->getLogParameters(),
                $event->getTime(),
            );
        } elseif ($event->getStatusName() === PlayerStatusEnum::DIRTY) {
            $holder = $event->getStatusHolder();
            if (!$holder instanceof Player) {
                throw new UnexpectedTypeException($holder, Player::class);
            }

            $this->roomLogService->createLog(
                LogEnum::SOILED,
                $event->getPlace(),
                $event->getVisibility(),
                'event_log',
                $holder,
                $event->getLogParameters(),
                $event->getTime(),
            );
        }
    }

    public function onStatusRemoved(StatusEvent $event): void
    {
        if ($event->getStatusName() === EquipmentStatusEnum::PLANT_YOUNG) {
            $this->roomLogService->createLog(
                PlantLogEnum::PLANT_MATURITY,
                $event->getPlace(),
                $event->getVisibility(),
                'event_log',
                null,
                $event->getLogParameters(),
                $event->getTime(),
            );
        }
    }
}
