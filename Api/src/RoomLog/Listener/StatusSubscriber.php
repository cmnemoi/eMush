<?php

namespace Mush\RoomLog\Listener;

use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
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
                $holder->getPlace(),
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
                $holder->getPlace(),
                $event->getVisibility(),
                'event_log',
                $holder,
                $event->getLogParameters(),
                $event->getTime(),
            );
        }
    }
}
