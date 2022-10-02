<?php

namespace Mush\Daedalus\Listener;

use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Game\Service\EventServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerSubscriber implements EventSubscriberInterface
{
    private EventServiceInterface $eventService;

    public function __construct(
        EventServiceInterface $eventService
    ) {
        $this->eventService = $eventService;
    }

    public static function getSubscribedEvents()
    {
        return [
            PlayerEvent::NEW_PLAYER => 'onNewPlayer',
            PlayerEvent::DEATH_PLAYER => 'onDeathPlayer',
        ];
    }

    public function onNewPlayer(PlayerEvent $event): void
    {
        $player = $event->getPlayer();
        $daedalus = $player->getDaedalus();

        if ($daedalus->getPlayers()->count() === $daedalus->getGameConfig()->getMaxPlayer()) {
            $fullDaedalusEvent = new DaedalusEvent(
                $daedalus,
                $event->getReasons()[0],
                $event->getTime()
            );
            $this->eventService->callEvent($fullDaedalusEvent, DaedalusEvent::FULL_DAEDALUS);
        } elseif ($daedalus->getPlayers()->count() === 1) {
            $startDaedalusEvent = new DaedalusEvent(
                $daedalus,
                $event->getReasons()[0],
                $event->getTime()
            );
            $this->eventService->callEvent($startDaedalusEvent, DaedalusEvent::START_DAEDALUS);
        }
    }

    public function onDeathPlayer(PlayerEvent $event): void
    {
        $player = $event->getPlayer();
        $reason = $event->getReasons()[0];

        if ($player->getDaedalus()->getPlayers()->getPlayerAlive()->isEmpty() &&
            !in_array($reason, [EndCauseEnum::SOL_RETURN, EndCauseEnum::EDEN, EndCauseEnum::SUPER_NOVA, EndCauseEnum::KILLED_BY_NERON]) &&
            $player->getDaedalus()->getGameStatus() !== GameStatusEnum::STARTING
        ) {
            $endDaedalusEvent = new DaedalusEvent(
                $player->getDaedalus(),
                EndCauseEnum::DAEDALUS_DESTROYED,
                $event->getTime()
            );

            $this->eventService->callEvent($endDaedalusEvent, DaedalusEvent::END_DAEDALUS);
        }
    }
}
