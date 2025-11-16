<?php

namespace Mush\Place\Listener;

use Mush\Place\Entity\Place;
use Mush\Status\Enum\PlaceStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Event\StatusEvent;
use Mush\Status\Service\StatusCycleHandlerServiceInterface;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\File\Exception\UnexpectedTypeException;

class StatusSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private StatusServiceInterface $statusService,
        private StatusCycleHandlerServiceInterface $cycleHandlerService,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            StatusEvent::STATUS_REMOVED => 'onStatusRemoved',
        ];
    }

    public function onStatusRemoved(StatusEvent $event): void
    {
        $statusName = $event->getStatusName();
        match ($statusName) {
            PlaceStatusEnum::SELECTED_FOR_FIRE->toString() => $this->createFireAndMakeItDestroy(event: $event),
            default => null,
        };
    }

    private function createFireAndMakeItDestroy(StatusEvent $event)
    {
        $place = $event->getStatusHolder();

        if (!$place instanceof Place) {
            throw new UnexpectedTypeException($place, Place::class);
        }

        $fire = $this->statusService->createStatusFromName(
            StatusEnum::FIRE,
            $place,
            $event->getTags(),
            $event->getTime()
        );

        $cycleHandler = $this->cycleHandlerService->getStatusCycleHandler($fire);

        $cycleHandler?->handleNewCycle($fire, $place, $event->getTime());
    }
}
