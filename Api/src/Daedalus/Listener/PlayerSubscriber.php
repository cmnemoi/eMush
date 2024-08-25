<?php

namespace Mush\Daedalus\Listener;

use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerSubscriber implements EventSubscriberInterface
{
    private EventServiceInterface $eventService;
    private DaedalusServiceInterface $daedalusService;

    public function __construct(
        DaedalusServiceInterface $daedalusService,
        EventServiceInterface $eventService
    ) {
        $this->daedalusService = $daedalusService;
        $this->eventService = $eventService;
    }

    public static function getSubscribedEvents(): array
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
                $event->getTags(),
                $event->getTime()
            );
            $this->eventService->callEvent($startDaedalusEvent, DaedalusEvent::START_DAEDALUS);
        }

        if ($daedalus->getPlayers()->count() === $daedalus->getGameConfig()->getMaxPlayer()) {
            $fullDaedalusEvent = new DaedalusEvent(
                $daedalus,
                $event->getTags(),
                $event->getTime()
            );
            $this->eventService->callEvent($fullDaedalusEvent, DaedalusEvent::FULL_DAEDALUS);
        }
    }

    public function onDeathPlayer(PlayerEvent $event): void
    {
        $player = $event->getPlayer();
        $reasons = $event->getTags();
        $endCause = $event->mapLog(EndCauseEnum::DEATH_CAUSE_MAP);

        if (!\in_array($endCause, [EndCauseEnum::SOL_RETURN, EndCauseEnum::EDEN, EndCauseEnum::SUPER_NOVA, EndCauseEnum::KILLED_BY_NERON], true)
            && $player->getDaedalus()->getGameStatus() !== GameStatusEnum::STARTING
            && $player->getDaedalus()->getPlayers()->getPlayerAlive()->isEmpty()
        ) {
            $endDaedalusEvent = new DaedalusEvent(
                $player->getDaedalus(),
                [EndCauseEnum::DAEDALUS_DESTROYED],
                $event->getTime()
            );

            $this->eventService->callEvent($endDaedalusEvent, DaedalusEvent::FINISH_DAEDALUS);
        }
    }

    public function onEndPlayer(PlayerEvent $event): void
    {
        $daedalus = $event->getPlayer()->getDaedalus();

        if ($daedalus->getGameStatus() === GameStatusEnum::FINISHED
            && $daedalus->getPlayers()->filter(static fn (Player $player) => $player->getPlayerInfo()->getGameStatus() !== GameStatusEnum::CLOSED)->isEmpty()
        ) {
            $this->daedalusService->closeDaedalus($daedalus, $event->getTags(), $event->getTime());
        }
    }
}
