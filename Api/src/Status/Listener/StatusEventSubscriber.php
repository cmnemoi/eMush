<?php

declare(strict_types=1);

namespace Mush\Status\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;
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
        $statusHolder = $event->getStatusHolder();

        if ($event->getStatusName() === EquipmentStatusEnum::BROKEN) {
            $this->createNoGravityStatus($statusHolder, $event->getTags(), $event->getTime());
            $this->ejectFocusedPlayers($statusHolder, $event->getTags(), $event->getTime());
            $this->makeLaidDownPlayersGetUp($statusHolder, $event->getTags(), $event->getTime());
        }

        if ($event->getStatusName() === PlayerStatusEnum::MUSH) {
            $this->statusService->removeStatus(
                PlayerStatusEnum::STARVING,
                $statusHolder,
                $event->getTags(),
                $event->getTime()
            );
        }
    }

    public function onStatusRemoved(StatusEvent $event): void
    {
        $statusHolder = $event->getStatusHolder();

        switch ($event->getStatusName()) {
            case DaedalusStatusEnum::TRAVELING:
                $daedalusEvent = new DaedalusEvent(
                    $event->getDaedalus(),
                    $event->getTags(),
                    $event->getTime()
                );
                $this->eventService->callEvent($daedalusEvent, DaedalusEvent::TRAVEL_FINISHED);
                break;
            case EquipmentStatusEnum::BROKEN:
                $this->repairScrewedTalkie($event->getStatus(), $event->getTags(), $event->getTime());
                $this->handleRepairGravity($statusHolder, $event->getTags(), $event->getTime());
                break;
            default:
        }
    }

    private function createNoGravityStatus(
        StatusHolderInterface $statusHolder,
        array $tags,
        \DateTime $time
    ): void {
        if ($statusHolder instanceof GameEquipment
            && $statusHolder->getName() === EquipmentEnum::GRAVITY_SIMULATOR
        ) {
            $daedalus = $statusHolder->getDaedalus();

            $this->statusService->createStatusFromName(
                DaedalusStatusEnum::NO_GRAVITY,
                $daedalus,
                $tags,
                $time
            );
        }
    }

    private function ejectFocusedPlayers(
        StatusHolderInterface $statusHolder,
        array $tags,
        \DateTime $time
    ): void {
        if (
            $statusHolder instanceof GameEquipment
            && $statusHolder->getEquipment()->hasAction(ActionEnum::ACCESS_TERMINAL)
        ) {
            foreach ($statusHolder->getPlace()->getPlayers()->getPlayerAlive() as $player) {
                if ($player->getStatusByName(PlayerStatusEnum::FOCUSED)?->getTarget()?->getName() === $statusHolder->getName()) {
                    $this->statusService->removeStatus(
                        PlayerStatusEnum::FOCUSED,
                        $player,
                        $tags,
                        $time
                    );
                }
            }
        }
    }

    private function makeLaidDownPlayersGetUp(
        StatusHolderInterface $statusHolder,
        array $tags,
        \DateTime $time
    ): void {
        if ($statusHolder instanceof GameEquipment
            && $statusHolder->getEquipment()->hasAction(ActionEnum::LIE_DOWN)
        ) {
            foreach ($statusHolder->getPlace()->getPlayers()->getPlayerAlive() as $player) {
                if ($player->getStatusByName(PlayerStatusEnum::LYING_DOWN)?->getTarget()?->getName() === $statusHolder->getName()) {
                    $this->statusService->removeStatus(
                        PlayerStatusEnum::LYING_DOWN,
                        $player,
                        $tags,
                        $time,
                        VisibilityEnum::PUBLIC,
                    );
                }
            }
        }
    }

    private function repairScrewedTalkie(
        Status $brokenStatus,
        array $tags,
        \DateTime $time
    ): void {
        $brokenStatusHolder = $brokenStatus->getOwner();
        // If so, remove the screwed talkie status from the pirate
        if ($brokenStatusHolder instanceof GameItem
            && in_array($brokenStatusHolder->getName(), [ItemEnum::ITRACKIE, ItemEnum::WALKIE_TALKIE])
        ) {
            /** @var Player $player */
            foreach ($brokenStatusHolder->getDaedalus()->getPlayers()->getPlayerAlive() as $player) {
                $talkieScrewedStatus = $player->getStatusByName(PlayerStatusEnum::TALKIE_SCREWED);
                if ($talkieScrewedStatus && $talkieScrewedStatus->getTarget() === $brokenStatusHolder->getOwner()) {
                    $this->statusService->removeStatus(
                        PlayerStatusEnum::TALKIE_SCREWED,
                        $player,
                        $tags,
                        $time
                    );
                }
            }
        }
    }

    private function handleRepairGravity(
        StatusHolderInterface $statusHolder,
        array $tags,
        \DateTime $time
    ): void {
        if ($statusHolder instanceof GameEquipment
            && $statusHolder->getName() === EquipmentEnum::GRAVITY_SIMULATOR
        ) {
            $daedalus = $statusHolder->getDaedalus();

            $this->statusService->removeStatus(
                DaedalusStatusEnum::NO_GRAVITY,
                $daedalus,
                $tags,
                $time
            );

            $this->statusService->createStatusFromName(
                DaedalusStatusEnum::NO_GRAVITY_REPAIRED,
                $daedalus,
                $tags,
                $time
            );
        }
    }
}
