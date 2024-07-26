<?php

namespace Mush\Status\Listener;

use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Event\PlayerCycleEvent;
use Mush\Status\Entity\Status;
use Mush\Status\Event\StatusCycleEvent;
use Mush\Status\Service\MakePlayerInactiveService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class PlayerCycleSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EventServiceInterface $eventService,
        private MakePlayerInactiveService $makePlayerInactiveService
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            PlayerCycleEvent::PLAYER_NEW_CYCLE => 'onNewCycle',
        ];
    }

    public function onNewCycle(PlayerCycleEvent $event): void
    {
        $player = $event->getPlayer();

        /** @var Status $status */
        foreach ($player->getStatuses() as $status) {
            $statusNewCycle = new StatusCycleEvent(
                $status,
                $player,
                $event->getTags(),
                $event->getTime()
            );
            $this->eventService->callEvent($statusNewCycle, StatusCycleEvent::STATUS_NEW_CYCLE);
        }

        $this->makePlayerInactiveService->execute($player);
    }
}
