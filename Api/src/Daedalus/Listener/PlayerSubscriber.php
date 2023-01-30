<?php

namespace Mush\Daedalus\Listener;

use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerSubscriber implements EventSubscriberInterface
{
    private EventDispatcherInterface $eventDispatcher;
    private DaedalusServiceInterface $daedalusService;

    public function __construct(
        DaedalusServiceInterface $daedalusService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->daedalusService = $daedalusService;
        $this->eventDispatcher = $eventDispatcher;
    }

    public static function getSubscribedEvents()
    {
        return [
            PlayerEvent::NEW_PLAYER => 'onNewPlayer',
            PlayerEvent::DEATH_PLAYER => ['onDeathPlayer', -10],
            PlayerEvent::END_PLAYER => ['onEndPlayer', -10],
        ];
    }

    public function onNewPlayer(PlayerEvent $event): void
    {
        $player = $event->getPlayer();
        $daedalus = $player->getDaedalus();

        if ($daedalus->getPlayers()->count() === 1) {
            $startDaedalusEvent = new DaedalusEvent(
                $daedalus,
                $event->getReason(),
                $event->getTime()
            );
            $this->eventDispatcher->dispatch($startDaedalusEvent, DaedalusEvent::START_DAEDALUS);
        }

        if ($daedalus->getPlayers()->count() === $daedalus->getGameConfig()->getMaxPlayer()) {
            $fullDaedalusEvent = new DaedalusEvent(
                $daedalus,
                $event->getReason(),
                $event->getTime()
            );
            $this->eventDispatcher->dispatch($fullDaedalusEvent, DaedalusEvent::FULL_DAEDALUS);
        }
    }

    public function onDeathPlayer(PlayerEvent $event): void
    {
        $player = $event->getPlayer();
        $reason = $event->getReason();

        if ($player->getDaedalus()->getPlayers()->getPlayerAlive()->isEmpty() &&
            !in_array($reason, [EndCauseEnum::SOL_RETURN, EndCauseEnum::EDEN, EndCauseEnum::SUPER_NOVA, EndCauseEnum::KILLED_BY_NERON]) &&
            $player->getDaedalus()->getGameStatus() !== GameStatusEnum::STARTING
        ) {
            $endDaedalusEvent = new DaedalusEvent(
                $player->getDaedalus(),
                EndCauseEnum::DAEDALUS_DESTROYED,
                $event->getTime()
            );

            $this->eventDispatcher->dispatch($endDaedalusEvent, DaedalusEvent::FINISH_DAEDALUS);
        }
    }

    public function onEndPlayer(PlayerEvent $event): void
    {
        $player = $event->getPlayer();
        $playerInfo = $player->getPlayerInfo();
        $daedalus = $player->getDaedalus();

        if ($daedalus->getPlayers()->filter(fn (Player $player) => $playerInfo->getGameStatus() !== GameStatusEnum::CLOSED)->isEmpty() &&
            $daedalus->getGameStatus() === GameStatusEnum::FINISHED
        ) {
            $this->daedalusService->closeDaedalus($daedalus, $event->getReason(), $event->getTime());
        }
    }
}
