<?php

declare(strict_types=1);

namespace Mush\Status\Listener;

use Mush\Daedalus\Enum\NeronCpuPriorityEnum;
use Mush\Daedalus\Event\NeronEvent;
use Mush\Status\Enum\PlayerStatusEnum;
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
        $this->removeOldCpuPriorityStatus($event);
        $this->createNewCpuPriorityStatus($event);

        $author = $event->getAuthor();
        if ($author !== null) {
            $this->statusService->createStatusFromName(
                statusName: PlayerStatusEnum::CHANGED_CPU_PRIORITY,
                holder: $author,
                tags: $event->getTags(),
                time: $event->getTime(),
            );
        }
    }

    private function removeOldCpuPriorityStatus(NeronEvent $event): void
    {
        $oldCpuPriority = $event->getTags()['oldCpuPriority'];

        if (!isset(NeronCpuPriorityEnum::$statusMap[$oldCpuPriority])) {
            return;
        }

        $statusToRemove = NeronCpuPriorityEnum::$statusMap[$oldCpuPriority];

        $this->statusService->removeStatus(
            statusName: $statusToRemove,
            holder: $event->getDaedalus(),
            tags: $event->getTags(),
            time: $event->getTime(),
        );
    }

    private function createNewCpuPriorityStatus(NeronEvent $event): void
    {
        if (!isset(NeronCpuPriorityEnum::$statusMap[$event->getNeron()->getCpuPriority()])) {
            return;
        }

        $statusToAdd = NeronCpuPriorityEnum::$statusMap[$event->getNeron()->getCpuPriority()];

        $this->statusService->createStatusFromName(
            statusName: $statusToAdd,
            holder: $event->getDaedalus(),
            tags: $event->getTags(),
            time: $event->getTime(),
        );
    }
}
