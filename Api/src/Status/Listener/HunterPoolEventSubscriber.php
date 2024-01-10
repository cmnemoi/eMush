<?php

declare(strict_types=1);

namespace Mush\Status\Listener;

use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Hunter\Event\HunterPoolEvent;
use Mush\Status\Enum\HunterStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class HunterPoolEventSubscriber implements EventSubscriberInterface
{
    private StatusServiceInterface $statusService;

    public function __construct(
        StatusServiceInterface $statusService
    ) {
        $this->statusService = $statusService;
    }

    public static function getSubscribedEvents()
    {
        return [
            HunterPoolEvent::UNPOOL_HUNTERS => ['onUnpoolHunters', EventPriorityEnum::LOW],
        ];
    }

    public function onUnpoolHunters(HunterPoolEvent $event): void
    {
        // if hunter spawn was triggered by a travel, remove truce cycle status so hunters shoot right away
        $daedalus = $event->getDaedalus();
        if ($event->hasTag(DaedalusEvent::TRAVEL_FINISHED)) {
            foreach ($daedalus->getAttackingHunters() as $hunter) {
                $this->statusService->removeStatus(
                    statusName: HunterStatusEnum::TRUCE_CYCLES,
                    holder: $hunter,
                    tags: $event->getTags(),
                    time: $event->getTime()
                );
            }
        }
    }
}
