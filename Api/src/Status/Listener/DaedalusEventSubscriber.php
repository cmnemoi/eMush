<?php

declare(strict_types=1);

namespace Mush\Status\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Exploration\Service\PlanetServiceInterface;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\Random\GetRandomIntegerServiceInterface;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\HunterStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class DaedalusEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private GetRandomIntegerServiceInterface $getRandomInteger,
        private PlanetServiceInterface $planetService,
        private StatusServiceInterface $statusService,
    ) {}

    public static function getSubscribedEvents()
    {
        return [
            DaedalusEvent::START_DAEDALUS => 'onStartDaedalus',
            DaedalusEvent::TRAVEL_LAUNCHED => ['onTravelLaunched', EventPriorityEnum::HIGH],
            DaedalusEvent::TRAVEL_FINISHED => ['onTravelFinished', EventPriorityEnum::LOW],
        ];
    }

    public function onStartDaedalus(DaedalusEvent $event): void
    {
        $this->setupRebelBaseContactDuration($event);
    }

    public function onTravelLaunched(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();

        $this->createDaedalusStatusFromName(DaedalusStatusEnum::TRAVELING, $event);

        if ($this->planetService->findOneByDaedalusDestination($daedalus) !== null) {
            $this->createDaedalusStatusFromName(DaedalusStatusEnum::IN_ORBIT, $event);
        }
        if ($event->hasTag(ActionEnum::LEAVE_ORBIT->value)) {
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

    private function setupRebelBaseContactDuration(DaedalusEvent $event): void
    {
        $min = $event->getDaedalus()->getDaedalusConfig()->getRebelBaseContactDurationMin();
        $max = $event->getDaedalus()->getDaedalusConfig()->getRebelBaseContactDurationMax();

        /** @var ChargeStatus $chargeStatus */
        $chargeStatus = $this->createDaedalusStatusFromName(DaedalusStatusEnum::REBEL_BASE_CONTACT_DURATION, $event);
        $this->statusService->updateCharge(
            chargeStatus: $chargeStatus,
            delta: $this->getRandomInteger->execute($min, $max),
            tags: $event->getTags(),
            time: $event->getTime(),
            mode: VariableEventInterface::SET_VALUE,
        );
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
        foreach ($daedalus->getHuntersAroundDaedalus()->getAllHuntersByType(HunterEnum::HUNTER) as $hunter) {
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
     *
     * Trail reducer project provides an additional reduction (stored in modifier delta).
     */
    private function getNumberOfCatchingUpHunters(Daedalus $daedalus): int
    {
        $huntersLeft = $daedalus->getHuntersAroundDaedalus()->getAllHuntersByType(HunterEnum::HUNTER)->count();
        $reductionRate = $this->getHunterReductionRate($daedalus);

        if ($huntersLeft > 0) {
            return (int) ceil($reductionRate * $huntersLeft);
        }

        $hunterDrawCost = $daedalus->getGameConfig()->getHunterConfigs()->getHunter(HunterEnum::HUNTER)?->getDrawCost();

        return (int) ceil($reductionRate * $daedalus->getHunterPoints() / $hunterDrawCost) ?: 1;
    }

    /**
     * Returns the rate of hunters that will catch up after travel.
     * Base rate is from config (default 50%). Trail reducer adds an extra reduction (25%).
     */
    private function getHunterReductionRate(Daedalus $daedalus): float
    {
        $baseReduction = $daedalus->getGameConfig()->getDifficultyConfig()->getFollowingHuntersPercentage() / 100;
        $trailReducerBonus = $this->getTrailReducerBonus($daedalus);

        return $baseReduction - $trailReducerBonus;
    }

    private function getTrailReducerBonus(Daedalus $daedalus): float
    {
        if (!$daedalus->hasModifierByModifierName(ModifierNameEnum::TRAIL_REDUCER_MODIFIER)) {
            return 0.0;
        }

        return $daedalus
            ->getModifiers()
            ->getByModifierNameOrThrow(ModifierNameEnum::TRAIL_REDUCER_MODIFIER)
            ->getVariableModifierConfigOrThrow()
            ->getDelta();
    }
}
