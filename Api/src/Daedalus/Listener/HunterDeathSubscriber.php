<?php

namespace Mush\Daedalus\Listener;

use Mush\Daedalus\Repository\DaedalusRepositoryInterface;
use Mush\Hunter\Event\HunterEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class HunterDeathSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private DaedalusRepositoryInterface $daedalusRepository,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            HunterEvent::HUNTER_DEATH => 'onHunterDeath',
        ];
    }

    public function onHunterDeath(HunterEvent $event): void
    {
        if ($event->getHunter()->isTransport()) {
            return;
        }

        $event->getDaedalusStatistics()->changeShipsDestroyed(1);

        $this->daedalusRepository->save($event->getDaedalus());
    }
}
