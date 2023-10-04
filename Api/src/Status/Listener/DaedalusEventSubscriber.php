<?php

declare(strict_types=1);

namespace Mush\Status\Listener;

use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class DaedalusEventSubscriber implements EventSubscriberInterface
{
    private StatusServiceInterface $statusService;

    public function __construct(StatusServiceInterface $statusService)
    {
        $this->statusService = $statusService;
    }

    public static function getSubscribedEvents()
    {
        return [
            DaedalusEvent::TRAVEL_LAUNCHED => 'onTravelLaunched',
        ];
    }

    public function onTravelLaunched(DaedalusEvent $event): void
    {
        $this->statusService->createStatusFromName(
            statusName: DaedalusStatusEnum::TRAVELING,
            holder: $event->getDaedalus(),
            tags: $event->getTags(),
            time: new \DateTime(),
        );
    }
}
