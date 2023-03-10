<?php

namespace Mush\Status\Listener;

use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StatusSubscriber implements EventSubscriberInterface
{
    private StatusServiceInterface $statusService;
    protected EventServiceInterface $eventService;

    public function __construct(
        StatusServiceInterface $statusService,
        EventServiceInterface $eventService,
    ) {
        $this->statusService = $statusService;
        $this->eventService = $eventService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StatusEvent::STATUS_APPLIED => [['onStatusApplied', 1000], ['addStatusConfig', 999]],
            StatusEvent::STATUS_REMOVED => [['onStatusRemoved', -10], ['addStatusConfig', 1001]],
        ];
    }

    public function onStatusApplied(StatusEvent $event): void
    {
        if ($event->getStatusConfig() === null) {
            $this->statusService->createStatusFromName(
                $event->getStatusName(),
                $event->getPlace()->getDaedalus(),
                $event->getStatusHolder(),
                $event->getTime(),
                $event->getStatusTarget()
            );
        }
    }

    public function onStatusRemoved(StatusEvent $event): void
    {
        $holder = $event->getStatusHolder();
        $status = $holder->getStatusByName($event->getStatusName());

        if ($status === null) {
            throw new \LogicException("the eventHolder should have this status {$event->getStatusName()}");
        }

        // If a talkie or itrackie is repaired, check if it was screwed.
        $this->handleScrewedTalkie($event);

        $this->statusService->delete($status);
    }

    public function addStatusConfig(StatusEvent $event): void
    {
        $statusConfig = $this->statusService->getStatusConfigByNameAndDaedalus($event->getStatusName(), $event->getPlace()->getDaedalus());
        $event->setStatusConfig($statusConfig);
    }

    private function handleScrewedTalkie(StatusEvent $event): void
    {
        $holder = $event->getStatusHolder();

        // If so, remove the screwed talkie status from the owner of the talkie and the pirate
        if ($holder instanceof GameItem &&
            in_array($holder->getName(), [ItemEnum::ITRACKIE, ItemEnum::WALKIE_TALKIE]) &&
            $event->getStatusName() === EquipmentStatusEnum::BROKEN
        ) {
            /** @var Player $piratedPlayer */
            $piratedPlayer = $holder->getOwner();

            $screwedTalkieStatus = $this->statusService->getByTargetAndName($piratedPlayer, PlayerStatusEnum::TALKIE_SCREWED);
            if ($screwedTalkieStatus !== null) {
                $removeEvent = new StatusEvent(
                    PlayerStatusEnum::TALKIE_SCREWED,
                    $screwedTalkieStatus->getOwner(),
                    $event->getTags(),
                    $event->getTime()
                );
                $this->eventService->callEvent($removeEvent, StatusEvent::STATUS_REMOVED);
            }
        }
    }
}
