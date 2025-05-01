<?php

declare(strict_types=1);

namespace Mush\Triumph\Listener;

use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Triumph\Service\ChangeTriumphFromEventService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class DaedalusCycleEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ChangeTriumphFromEventService $changeTriumphFromEventService,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            DaedalusCycleEvent::DAEDALUS_NEW_CYCLE => ['onNewCycle', EventPriorityEnum::PLAYER_TRIUMPH],
        ];
    }

    public function onNewCycle(DaedalusCycleEvent $event): void
    {
        $this->changeTriumphFromEventService->execute($event);
    }
}
