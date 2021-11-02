<?php

namespace Mush\RoomLog\Listener;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\PlantLogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
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
        $holder = $event->getStatusHolder();
        $statusName = $event->getStatusName();

        switch ($statusName) {
            case PlayerStatusEnum::STARVING:
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

                return;

            //@TODO add pregnancy log when logs will have been refactored
            // case PlayerStatusEnum::PREGNANT:
            //     if (!$holder instanceof Player) {
            //         throw new UnexpectedTypeException($holder, Player::class);
            //     }

            //     $this->roomLogService->createLog(
            //         LogEnum::BECOME_PREGNANT,
            //         $event->getPlace(),
            //         VisibilityEnum::PRIVATE,
            //         'event_log',
            //         $holder,
            //         $event->getLogParameters(),
            //         $event->getTime(),
            //     );

            case PlayerStatusEnum::DIRTY:
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

                // no break
            case EquipmentStatusEnum::BROKEN:
                $rooms = new ArrayCollection([]);
                if ($holder instanceof Door) {
                    $rooms = $holder->getRooms()->toArray();
                } elseif ($holder instanceof GameEquipment) {
                    $rooms = [$holder->getPlace()];
                }

                foreach ($rooms as $room) {
                    $this->roomLogService->createLog(
                        LogEnum::EQUIPMENT_BROKEN,
                        $room,
                        $event->getVisibility(),
                        'event_log',
                        null,
                        $event->getLogParameters(),
                        $event->getTime()
                    );
                }
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
