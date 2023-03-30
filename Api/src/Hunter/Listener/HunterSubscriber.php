<?php

namespace Mush\Hunter\Listener;

use Mush\Hunter\Event\HunterEvent;
use Mush\Hunter\Event\HunterPoolEvent;
use Mush\Hunter\Service\HunterServiceInterface;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class HunterSubscriber implements EventSubscriberInterface
{
    private HunterServiceInterface $hunterService;
    private RoomLogServiceInterface $roomLogService;

    public function __construct(HunterServiceInterface $hunterService, RoomLogServiceInterface $roomLogService)
    {
        $this->hunterService = $hunterService;
        $this->roomLogService = $roomLogService;
    }

    public static function getSubscribedEvents()
    {
        return [
            HunterEvent::HUNTER_DEATH => ['onHunterDeath', -100],
            HunterPoolEvent::UNPOOL_HUNTERS => 'onUnpoolHunters',
            HunterPoolEvent::POOL_HUNTERS => 'onPoolHunters',
        ];
    }

    public function onHunterDeath(HunterEvent $event): void
    {
        $hunter = $event->getHunter();
        $player = $event->getPlayer();

        if (!$player) {
            throw new \Exception('HunterEvent should have a Player');
        }

        $this->roomLogService->createLog(
            'hunter_death',
            $player->getPlace(),
            $event->getVisibility(),
            'event_log',
            $player,
            [
                $hunter->getLogKey() => $hunter->getName(),
                $player->getLogKey() => $player->getLogName(),
            ],
            $event->getTime()
        );
        $this->hunterService->killHunter($hunter);
    }

    public function onUnpoolHunters(HunterPoolEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $nbHuntersToUnpool = $event->getNbHunters();

        $this->hunterService->unpoolHunters($daedalus, $nbHuntersToUnpool);
    }

    public function onPoolHunters(HunterPoolEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $nbHuntersToPool = $event->getNbHunters();

        $this->hunterService->putHuntersInPool($daedalus, $nbHuntersToPool);
    }
}
