<?php

namespace Mush\Status\Listener;

use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Event\ChargeStatusEvent;
use Mush\Status\Event\StatusEvent;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

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
        $statusConfig = $this->statusService->getStatusConfigByNameAndDaedalus(
            $event->getStatusName(),
            $event->getPlace()->getDaedalus()
        );

        if ($event instanceof ChargeStatusEvent) {
            if (!$statusConfig instanceof ChargeStatusConfig) {
                throw new UnexpectedTypeException($statusConfig, ChargeStatusConfig::class);
            }

            $status = $this->statusService->createChargeStatusFromConfig(
                $statusConfig,
                $event->getStatusHolder(),
                $event->getInitCharge(),
                $event->getThreshold(),
                $event->getDischargeStrategy(),
                $event->getStatusTarget()
            );
        } else {
            $status = $this->statusService->createStatusFromConfig(
                $statusConfig,
                $event->getStatusHolder(),
                $event->getStatusTarget()
            );
        }

        $this->statusService->persist($status);
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
}
