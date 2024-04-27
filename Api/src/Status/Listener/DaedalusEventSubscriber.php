<?php

declare(strict_types=1);

namespace Mush\Status\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Exploration\Service\PlanetServiceInterface;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;
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
            DaedalusEvent::TRAVEL_FINISHED => ['onTravelFinished', EventPriorityEnum::LOW],
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
        $this->createTruceStatusForHunters($event);

        $this->updateNumberOfCatchingUpHunters($event);
    }

    public function onTravelFinished(DaedalusEvent $event): void
    {
        $this->resetNumberOfCatchingUpHunters($event);
    }

    private function createDaedalusStatusFromName(string $name, DaedalusEvent $event): Status
    {
        return $this->statusService->createStatusFromName(
            statusName: $name,
            holder: $event->getDaedalus(),
            tags: $event->getTags(),
            time: $event->getTime(),
        );
    }

    private function createTruceStatusForHunters(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();

        /** @var Hunter $hunter */
        foreach ($daedalus->getAttackingHunters()->getAllHuntersByType(HunterEnum::HUNTER) as $hunter) {
            /** @var ?ChargeStatus $truceStatus */
            $truceStatus = $hunter->getStatusByName(HunterStatusEnum::TRUCE_CYCLES);
            if ($truceStatus) {
                $this->statusService->updateCharge(
                    chargeStatus: $truceStatus,
                    delta: (int) $truceStatus->getThreshold(),
                    tags: $event->getTags(),
                    time: $event->getTime()
                );

                continue;
            }
            $this->statusService->createStatusFromName(
                statusName: HunterStatusEnum::TRUCE_CYCLES,
                holder: $hunter,
                tags: $event->getTags(),
                time: $event->getTime()
            );
        }
    }

    private function updateNumberOfCatchingUpHunters(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();

        /** @var ?ChargeStatus $followingHuntersStatus */
        $followingHuntersStatus = $daedalus->getStatusByName(DaedalusStatusEnum::FOLLOWING_HUNTERS);
        if (!$followingHuntersStatus) {
            /** @var ChargeStatus $followingHuntersStatus */
            $followingHuntersStatus = $this->createDaedalusStatusFromName(DaedalusStatusEnum::FOLLOWING_HUNTERS, $event);
        }

        $numberOfCatchingUpHunters = $this->getNumberOfCatchingUpHunters($daedalus);

        $this->statusService->updateCharge(
            chargeStatus: $followingHuntersStatus,
            delta: $numberOfCatchingUpHunters,
            tags: $event->getTags(),
            time: $event->getTime()
        );
    }

    private function removeInOrbitStatus(DaedalusEvent $event): void
    {
        $this->statusService->removeStatus(
            statusName: DaedalusStatusEnum::IN_ORBIT,
            holder: $event->getDaedalus(),
            tags: $event->getTags(),
            time: $event->getTime()
        );
    }

    private function resetNumberOfCatchingUpHunters(DaedalusEvent $event): void
    {
        /** @var ?ChargeStatus $followingHuntersStatus */
        $followingHuntersStatus = $event->getDaedalus()->getStatusByName(DaedalusStatusEnum::FOLLOWING_HUNTERS);
        if (!$followingHuntersStatus) {
            throw new \RuntimeException('Following hunters status not found');
        }

        $this->statusService->updateCharge(
            chargeStatus: $followingHuntersStatus,
            delta: -$followingHuntersStatus->getCharge(),
            tags: $event->getTags(),
            time: $event->getTime()
        );
    }

    /**
     * By default, spawn half of the attacking hunters after travel.
     * If there are no attacking hunters, spawn a wave with half of the hunter points the daedalus has.
     * If there are not enough hunter points, spawn at least one hunter.
     */
    private function getNumberOfCatchingUpHunters(Daedalus $daedalus): int
    {
        $numberOfCatchingUpHunters = (int) ceil($daedalus->getAttackingHunters()->getAllHuntersByType(HunterEnum::HUNTER)->count() / 2);
        if ($numberOfCatchingUpHunters <= 0) {
            $hunterDrawCost = $daedalus->getGameConfig()->getHunterConfigs()->getHunter(HunterEnum::HUNTER)?->getDrawCost();
            $numberOfCatchingUpHunters = (int) (ceil($daedalus->getHunterPoints() / $hunterDrawCost / 2)) ?: 1;
        }

        return $numberOfCatchingUpHunters;
    }
}
