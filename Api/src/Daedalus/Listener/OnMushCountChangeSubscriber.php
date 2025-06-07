<?php

namespace Mush\Daedalus\Listener;

use Mush\Daedalus\Repository\DaedalusRepositoryInterface;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OnMushCountChangeSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private DaedalusRepositoryInterface $daedalusRepository,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            StatusEvent::STATUS_APPLIED => ['onMushStatusApplied', EventPriorityEnum::LOWEST],
            StatusEvent::STATUS_REMOVED => ['onMushStatusRemoved', EventPriorityEnum::LOWEST],
        ];
    }

    public function onMushStatusApplied(StatusEvent $event): void
    {
        if ($event->getStatusName() !== PlayerStatusEnum::MUSH) {
            return;
        }

        $event->getDaedalusStatistics()->changeMushAmount(1);

        $this->daedalusRepository->save($event->getDaedalus());
    }

    public function onMushStatusRemoved(StatusEvent $event): void
    {
        if ($event->getStatusName() !== PlayerStatusEnum::MUSH || $event->hasTag(PlayerEvent::DEATH_PLAYER)) {
            return;
        }

        $event->getDaedalusStatistics()->changeMushAmount(-1);

        $this->daedalusRepository->save($event->getDaedalus());
    }
}
