<?php

namespace Mush\Hunter\Listener;

use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Communication\Services\NeronMessageServiceInterface;
use Mush\Hunter\Event\HunterEvent;
use Mush\Hunter\Event\HunterPoolEvent;
use Mush\Hunter\Service\HunterServiceInterface;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class HunterSubscriber implements EventSubscriberInterface
{
    private HunterServiceInterface $hunterService;
    private NeronMessageServiceInterface $neronMessageService;
    private RoomLogServiceInterface $roomLogService;

    public function __construct(
        HunterServiceInterface $hunterService,
        NeronMessageServiceInterface $neronMessageService,
        RoomLogServiceInterface $roomLogService
    ) {
        $this->hunterService = $hunterService;
        $this->neronMessageService = $neronMessageService;
        $this->roomLogService = $roomLogService;
    }

    public static function getSubscribedEvents()
    {
        return [
            HunterEvent::HUNTER_DEATH => 'onHunterDeath',
            HunterPoolEvent::UNPOOL_HUNTERS => 'onUnpoolHunters',
        ];
    }

    public function onHunterDeath(HunterEvent $event): void
    {
        $hunter = $event->getHunter();
        $player = $event->getAuthor();

        if (!$player) {
            throw new \Exception('HunterEvent should have a Player');
        }

        $logKey = $event->mapLog(LogEnum::HUNTER_DEATH_LOG_ENUM);
        if (!$logKey) {
            throw new \Exception('HunterEvent should have a logKey');
        }

        $this->roomLogService->createLog(
            $logKey,
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
        $this->hunterService->unpoolHunters($daedalus, $event->getTime());

        $this->neronMessageService->createNeronMessage(
            NeronMessageEnum::HUNTER_ARRIVAL,
            $daedalus,
            [],
            $event->getTime(),
        );
    }
}
