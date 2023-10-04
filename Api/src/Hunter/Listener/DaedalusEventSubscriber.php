<?php

declare(strict_types=1);

namespace Mush\Hunter\Listener;

use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Hunter\Service\HunterServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class DaedalusEventSubscriber implements EventSubscriberInterface
{
    private HunterServiceInterface $hunterService;

    public function __construct(HunterServiceInterface $hunterService)
    {
        $this->hunterService = $hunterService;
    }

    public static function getSubscribedEvents()
    {
        return [
            DaedalusEvent::TRAVEL_LAUNCHED => 'onTravelLaunched',
        ];
    }

    public function onTravelLaunched(DaedalusEvent $event): void
    {
        $huntersToPutInPool = $event->getDaedalus()->getAttackingHunters()->getAllHuntersByType(HunterEnum::HUNTER);

        /** @var Hunter $hunter */
        foreach ($huntersToPutInPool as $hunter) {
            $hunter->putInPool();
        }
        $this->hunterService->persist($huntersToPutInPool->toArray());

        $huntersToDelete = $event->getDaedalus()->getAttackingHunters()->filter(
            fn (Hunter $hunter) => $hunter->getName() !== HunterEnum::TRAX
        );
        $this->hunterService->delete($huntersToDelete->toArray());
    }
}
