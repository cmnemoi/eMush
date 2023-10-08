<?php

namespace Mush\Alert\Listener;

use Mush\Alert\Service\AlertServiceInterface;
use Mush\Hunter\Event\HunterEvent;
use Mush\Hunter\Event\HunterPoolEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class HunterSubscriber implements EventSubscriberInterface
{
    private AlertServiceInterface $alertService;

    public function __construct(
        AlertServiceInterface $alertService
    ) {
        $this->alertService = $alertService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            HunterEvent::HUNTER_DEATH => 'onHunterDeath',
            HunterPoolEvent::UNPOOL_HUNTERS => 'onUnpoolHunters',
        ];
    }

    public function onHunterDeath(HunterEvent $event): void
    {
        $daedalus = $event->getHunter()->getDaedalus();
        $this->alertService->handleHunterDeath($daedalus);
    }

    public function onUnpoolHunters(HunterPoolEvent $event): void
    {
        if ($event->getDaedalus()->getAttackingHunters()->isEmpty()) {
            return;
        }

        $this->alertService->handleHunterArrival($event->getDaedalus());
    }
}
