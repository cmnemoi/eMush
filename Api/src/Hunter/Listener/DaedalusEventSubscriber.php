<?php

declare(strict_types=1);

namespace Mush\Hunter\Listener;

use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Hunter\Event\HunterPoolEvent;
use Mush\Hunter\Service\CreateHunterService;
use Mush\Hunter\Service\DeleteTransportService;
use Mush\Hunter\Service\HunterServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class DaedalusEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private CreateHunterService $createHunter,
        private DeleteTransportService $deleteTransport,
        private EventServiceInterface $eventService,
        private HunterServiceInterface $hunterService,
    ) {}

    public static function getSubscribedEvents()
    {
        return [
            DaedalusEvent::DELETE_DAEDALUS => ['onDeleteDaedalus', EventPriorityEnum::HIGHEST],
            DaedalusEvent::TRAVEL_LAUNCHED => 'onTravelLaunched',
            DaedalusEvent::TRAVEL_FINISHED => 'onTravelFinished',
        ];
    }

    public function onDeleteDaedalus(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $attackingHunters = $daedalus->getHuntersAroundDaedalus();
        $pooledHunters = $daedalus->getHunterPool();

        /** @var Hunter $hunter */
        foreach ($attackingHunters as $hunter) {
            $hunter->resetTarget();
        }

        /** @var Hunter $hunter */
        foreach ($pooledHunters as $hunter) {
            $hunter->resetTarget();
        }

        $this->hunterService->persist($attackingHunters->toArray());
        $this->hunterService->persist($pooledHunters->toArray());

        $transports = $daedalus->getHuntersAroundDaedalus()->getAllHuntersByType(HunterEnum::TRANSPORT);
        foreach ($transports as $transport) {
            $this->deleteTransport->byId($transport->getId());
        }
    }

    public function onTravelLaunched(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $huntersToPutInPool = $daedalus->getAttackingHunters()->getAllHuntersByType(HunterEnum::HUNTER);

        /** @var Hunter $hunter */
        foreach ($huntersToPutInPool as $hunter) {
            $hunter->putInPool();
        }
        $this->hunterService->persist($huntersToPutInPool->toArray());

        $huntersToDelete = $daedalus->getAttackingHunters()->getAllExceptType(HunterEnum::TRAX);
        $this->hunterService->delete($huntersToDelete->toArray());

        $transports = $daedalus->getHuntersAroundDaedalus()->getAllHuntersByType(HunterEnum::TRANSPORT);
        foreach ($transports as $transport) {
            $this->deleteTransport->byId($transport->getId());
        }
    }

    public function onTravelFinished(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();

        $hunterPoolEvent = new HunterPoolEvent(
            $daedalus,
            $event->getTags(),
            $event->getTime()
        );
        $hunterPoolEvent->addTag($event->getEventName());

        $this->eventService->callEvent($hunterPoolEvent, HunterPoolEvent::UNPOOL_HUNTERS);
    }
}
