<?php

declare(strict_types=1);

namespace Mush\Status\Listener;

use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class StatusEventSubscriber implements EventSubscriberInterface
{
    private EventServiceInterface $eventService;
    private StatusServiceInterface $statusService;

    public function __construct(EventServiceInterface $eventService, StatusServiceInterface $statusService)
    {
        $this->eventService = $eventService;
        $this->statusService = $statusService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StatusEvent::STATUS_APPLIED => 'onStatusApplied',
            StatusEvent::STATUS_REMOVED => 'onStatusRemoved',
        ];
    }

    public function onStatusApplied(StatusEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $statusHolder = $event->getStatusHolder();

        // if a terminal is broken, player should not be focused on it anymore
        if (!$statusHolder instanceof GameEquipment) {
            return;
        }

        if ($event->getStatusName() === EquipmentStatusEnum::BROKEN) {
            // /** @var Player $player */
            foreach ($statusHolder->getPlace()->getPlayers()->getPlayerAlive() as $player) {
                if ($player->getStatusByName(PlayerStatusEnum::FOCUSED)?->getTarget()?->getName() === $statusHolder->getName()) {
                    $this->statusService->removeStatus(
                        PlayerStatusEnum::FOCUSED,
                        $player,
                        $event->getTags(),
                        $event->getTime()
                    );
                }
            }
        }
    }

    public function onStatusRemoved(StatusEvent $event): void
    {
        if ($event->getStatusName() === DaedalusStatusEnum::TRAVELING) {
            $daedalusEvent = new DaedalusEvent(
                $event->getDaedalus(),
                $event->getTags(),
                $event->getTime()
            );
            $this->eventService->callEvent($daedalusEvent, DaedalusEvent::TRAVEL_FINISHED);
        }
    }
}
