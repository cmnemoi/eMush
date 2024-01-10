<?php

declare(strict_types=1);

namespace Mush\Status\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Exploration\Service\PlanetServiceInterface;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\HunterStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class DaedalusEventSubscriber implements EventSubscriberInterface
{
    private PlanetServiceInterface $planetService;
    private StatusServiceInterface $statusService;

    public function __construct(
        PlanetServiceInterface $planetService,
        StatusServiceInterface $statusService
    ) {
        $this->planetService = $planetService;
        $this->statusService = $statusService;
    }

    public static function getSubscribedEvents()
    {
        return [
            DaedalusEvent::TRAVEL_LAUNCHED => ['onTravelLaunched', EventPriorityEnum::HIGH],
        ];
    }

    public function onTravelLaunched(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();

        $this->createDaedalusStatusFromName(DaedalusStatusEnum::TRAVELING, $event);

        if ($this->planetService->findOneByDaedalusDestination($daedalus) !== null) {
            $this->createDaedalusStatusFromName(DaedalusStatusEnum::IN_ORBIT, $event);
        }
        if ($event->hasTag(ActionEnum::LEAVE_ORBIT)) {
            $this->removeInOrbitStatus($event);
        }

        // after a travel, hunter should not attack right away
        /** @var Hunter $hunter */
        foreach ($daedalus->getAttackingHunters()->getAllHuntersByType(HunterEnum::HUNTER) as $hunter) {
            $this->statusService->createStatusFromName(
                statusName: HunterStatusEnum::TRUCE_CYCLES,
                holder: $hunter,
                tags: $event->getTags(),
                time: $event->getTime(),
            );
        }
    }

    private function createDaedalusStatusFromName(string $name, DaedalusEvent $event): void
    {
        $this->statusService->createStatusFromName(
            statusName: $name,
            holder: $event->getDaedalus(),
            tags: $event->getTags(),
            time: $event->getTime(),
        );
    }

    private function removeInOrbitStatus(DaedalusEvent $event): void
    {
        $this->statusService->removeStatus(
            statusName: DaedalusStatusEnum::IN_ORBIT,
            holder: $event->getDaedalus(),
            tags: $event->getTags(),
            time: new \DateTime(),
        );
    }
}
