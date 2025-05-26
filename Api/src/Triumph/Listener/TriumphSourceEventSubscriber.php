<?php

declare(strict_types=1);

namespace Mush\Triumph\Listener;

use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Triumph\Service\ChangeTriumphFromEventService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class TriumphSourceEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ChangeTriumphFromEventService $changeTriumphFromEventService,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            DaedalusCycleEvent::DAEDALUS_NEW_CYCLE => ['onDaedalusNewCycle', EventPriorityEnum::PLAYER_TRIUMPH],
            DaedalusEvent::FINISH_DAEDALUS => ['onDaedalusFinish', EventPriorityEnum::HIGH],
            DaedalusEvent::FULL_DAEDALUS => ['onDaedalusFull', EventPriorityEnum::LOW],
        ];
    }

    public function onDaedalusNewCycle(DaedalusCycleEvent $event): void
    {
        $this->changeTriumphFromEventService->execute($event);
    }

    public function onDaedalusFinish(DaedalusEvent $event): void
    {
        $this->changeTriumphFromEventService->execute($event);
    }

    public function onDaedalusFull(DaedalusEvent $event): void
    {
        $this->changeTriumphFromEventService->execute($event);
    }
}
