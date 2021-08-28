<?php

namespace Mush\Status\Listener;

use Mush\Status\Event\StatusEvent;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StatusSubscriber implements EventSubscriberInterface
{
    private StatusServiceInterface $statusService;

    public function __construct(
        StatusServiceInterface $statusService
    ) {
        $this->statusService = $statusService;
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
        $statusConfig = $this->statusService->getStatusConfigByNameAndDaedalus($event->getStatusName(), $event->getPlace()->getDaedalus());

        $status = $this->statusService->createStatusFromConfig($statusConfig, $event->getStatusHolder(), $event->getStatusTarget());

        $this->statusService->persist($status);
    }
}
