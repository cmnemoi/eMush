<?php

namespace Mush\Status\Listener;

use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Place\Event\RoomEvent;
use Mush\Place\Service\PlaceServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RoomSubscriber implements EventSubscriberInterface
{
    private PlaceServiceInterface $placeService;
    private StatusServiceInterface $statusService;

    public function __construct(
        PlaceServiceInterface $placeService,
        StatusServiceInterface $statusService,
    ) {
        $this->placeService = $placeService;
        $this->statusService = $statusService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RoomEvent::STARTING_FIRE => 'onStartingFire',
            RoomEvent::STOP_FIRE => 'onStopFire',
        ];
    }

    public function onStartingFire(RoomEvent $event): void
    {
        $room = $event->getPlace();

        if ($room->getType() !== PlaceTypeEnum::ROOM) {
            throw new \LogicException('place should be a room');
        }

        $this->statusService->createChargeStatus(StatusEnum::FIRE,
            $event->getPlace(),
            ChargeStrategyTypeEnum::CYCLE_INCREMENT,
            null,
            VisibilityEnum::PUBLIC,
            VisibilityEnum::HIDDEN
        );
    }

    public function onStopFire(RoomEvent $event): void
    {
        $room = $event->getPlace();

        if (($fireStatus = $room->getStatusByName(StatusEnum::FIRE)) === null) {
            throw new \LogicException('room should have a fire to stop');
        }

        $room->removeStatus($fireStatus);
        $this->placeService->persist($room);
    }
}
