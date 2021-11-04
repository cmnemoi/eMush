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
            StatusEvent::STATUS_APPLIED => [['onStatusApplied', 1000], ['addStatusConfig', 999]],
            StatusEvent::STATUS_REMOVED => [['onStatusRemoved', -10], ['addStatusConfig', 1001]],
        ];
    }

    public function onStatusApplied(StatusEvent $event): void
    {
        if ($event->getStatusConfig() === null) {
            $this->statusService->createStatusFromName(
                $event->getStatusName(),
                $event->getPlace()->getDaedalus(),
                $event->getStatusHolder(),
                $event->getReason(),
                $event->getTime(),
                $event->getStatusTarget()
            );
        }
    }

    public function onStatusRemoved(StatusEvent $event): void
    {
        $holder = $event->getStatusHolder();
        $status = $holder->getStatusByName($event->getStatusName());

        if ($status === null) {
            return;
        }

        $this->statusService->delete($status);
    }

    public function addStatusConfig(StatusEvent $event): void
    {
        $statusConfig = $this->statusService->getStatusConfigByNameAndDaedalus($event->getStatusName(), $event->getPlace()->getDaedalus());
        $event->setStatusConfig($statusConfig);
    }
}
