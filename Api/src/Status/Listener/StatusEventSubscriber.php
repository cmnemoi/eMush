<?php

declare(strict_types=1);

namespace Mush\Status\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\ChargeStatus;
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

        // if a terminal is broken, player should not be focused on it anymore
        if (!$statusHolder instanceof GameEquipment) {
            return;
        }

        if ($event->getStatusName() === EquipmentStatusEnum::BROKEN) {
            $this->createNoGravityStatus($statusHolder, $event->getTags(), $event->getTime());
            $this->resetElectricCharges($statusHolder, $event->getTags(), $event->getTime());
            $this->ejectFocussedPlayers($statusHolder, $event->getTags(), $event->getTime());
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
                $this->repairScrewedTalkie($statusHolder, $event->getTags(), $event->getTime());
                $this->handleRepairGravity($statusHolder, $event->getTags(), $event->getTime());
                break;
            default:
        }
    }

    // on applying broken, electric charges are set to 0
    private function resetElectricCharges(
        StatusHolderInterface $statusHolder,
        array $tags,
        \DateTime $time
    ): void {
        if ($statusHolder instanceof GameEquipment
            && $statusHolder->hasStatus(EquipmentStatusEnum::ELECTRIC_CHARGES)
        ) {
            /** @var ChargeStatus $electricCharges */
            $electricCharges = $statusHolder->getStatusByName(EquipmentStatusEnum::ELECTRIC_CHARGES);

            $this->statusService->updateCharge(
                chargeStatus: $electricCharges,
                delta: 0,
                tags: $tags,
                time: $time,
                mode: VariableEventInterface::SET_VALUE
            );
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

    private function ejectFocussedPlayers(
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

    private function repairScrewedTalkie(
        StatusHolderInterface $holder,
        array $tags,
        \DateTime $time
    ): void {
        // If so, remove the screwed talkie status from the owner of the talkie and the pirate
        if ($holder instanceof GameItem
            && in_array($holder->getName(), [ItemEnum::ITRACKIE, ItemEnum::WALKIE_TALKIE])
        ) {
            /** @var Player $piratedPlayer */
            $piratedPlayer = $holder->getOwner();

            $this->statusService->removeStatus(PlayerStatusEnum::TALKIE_SCREWED, $piratedPlayer, $tags, $time);
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
