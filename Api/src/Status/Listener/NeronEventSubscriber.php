<?php

declare(strict_types=1);

namespace Mush\Status\Listener;

use Mush\Daedalus\Event\NeronEvent;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class NeronEventSubscriber implements EventSubscriberInterface
{
    private StatusServiceInterface $statusService;

    public function __construct(StatusServiceInterface $statusService)
    {
        $this->statusService = $statusService;
    }

    public static function getSubscribedEvents()
    {
        return [
            NeronEvent::CPU_PRIORITY_CHANGED => 'onCpuPriorityChanged',
        ];
    }

    public function onCpuPriorityChanged(NeronEvent $event): void
    {
        $this->statusService->createStatusFromName(
            statusName: DaedalusStatusEnum::ASTRONAVIGATION_NERON_CPU_PRIORITY,
            holder: $event->getDaedalus(),
            tags: $event->getTags(),
            time: $event->getTime(),
        );
    }
}
