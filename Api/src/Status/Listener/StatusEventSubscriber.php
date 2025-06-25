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
        $statusName = $event->getStatusName();

        match ($statusName) {
            EquipmentStatusEnum::BROKEN => $this->handleBrokenEquipment($statusHolder, $event->getTags(), $event->getTime()),
            PlayerStatusEnum::MUSH => $this->handleMushStatusApplied($event),
            PlayerStatusEnum::INACTIVE, PlayerStatusEnum::HIGHLY_INACTIVE => $this->removeStatusFromPlayer(PlayerStatusEnum::PARIAH, $event),
            default => null,
        };
    }

    public function onStatusRemoved(StatusEvent $event): void
    {
        $statusHolder = $event->getStatusHolder();

        match ($event->getStatusName()) {
            DaedalusStatusEnum::TRAVELING => $this->dispatchTravelFinishedEvent($event),
            EquipmentStatusEnum::BROKEN => $this->handleEquipmentRepaired($event),
            PlayerStatusEnum::SLIME_TRAP => $this->statusService->createStatusFromName(
                PlayerStatusEnum::DIRTY,
                $statusHolder,
                $event->getTags(),
                $event->getTime()
            ),
            EquipmentStatusEnum::SLIMED => $this->statusService->createStatusFromName(
                statusName: EquipmentStatusEnum::BROKEN,
                holder: $statusHolder,
                tags: $event->getTags(),
                time: $event->getTime()
            ),
            PlayerStatusEnum::MUSH => $this->handleMushStatusRemoved($event),
            PlayerStatusEnum::LYING_DOWN => $this->removeStatusFromPlayer(PlayerStatusEnum::FITFUL_SLEEP, $event),
            default => null,
        };
    }

    private function handleBrokenEquipment(
        StatusHolderInterface $statusHolder,
        array $tags,
        \DateTime $time
    ): void {
        $this->createNoGravityStatus($statusHolder, $tags, $time);
        $this->ejectFocusedPlayers($statusHolder, $tags, $time);
        $this->makeLaidDownPlayersGetUp($statusHolder, $tags, $time);
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

    private function handleMushStatusApplied(StatusEvent $event): void
    {
        $this->removeStatusFromPlayer(PlayerStatusEnum::STARVING, $event);
    }

    private function handleMushStatusRemoved(StatusEvent $event): void
    {
        $this->removeStatusFromPlayer(PlayerStatusEnum::BERZERK, $event);
    }

    private function dispatchTravelFinishedEvent(StatusEvent $event): void
    {
        $daedalusEvent = new DaedalusEvent(
            daedalus: $event->getDaedalus(),
            tags: $event->getTags(),
            time: $event->getTime()
        );
        $this->eventService->callEvent($daedalusEvent, DaedalusEvent::TRAVEL_FINISHED);
    }

    private function handleEquipmentRepaired(StatusEvent $event): void
    {
        if ($event->hasTag(DaedalusEvent::DELETE_DAEDALUS)) {
            return;
        }

        $this->repairScrewedTalkie($event->getStatus(), $event->getTags(), $event->getTime());
        $this->handleRepairGravity($event->getStatusHolder(), $event->getTags(), $event->getTime());
    }

    private function repairScrewedTalkie(
        Status $brokenStatus,
        array $tags,
        \DateTime $time
    ): void {
        $brokenStatusHolder = $brokenStatus->getOwner();
        // If so, remove the screwed talkie status from the pirate
        if ($brokenStatusHolder instanceof GameItem
            && \in_array($brokenStatusHolder->getName(), [ItemEnum::ITRACKIE, ItemEnum::WALKIE_TALKIE], true)
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
        if ($statusHolder->getName() === EquipmentEnum::GRAVITY_SIMULATOR) {
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

    private function removeStatusFromPlayer(string $statusName, StatusEvent $event): void
    {
        $this->statusService->removeStatus(
            $statusName,
            $event->getPlayerStatusHolder(),
            $event->getTags(),
            $event->getTime()
        );
    }
}
