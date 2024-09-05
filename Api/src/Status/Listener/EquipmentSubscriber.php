<?php

namespace Mush\Status\Listener;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\TransformEquipmentEvent;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class EquipmentSubscriber implements EventSubscriberInterface
{
    private StatusServiceInterface $statusService;

    public function __construct(
        StatusServiceInterface $statusService,
    ) {
        $this->statusService = $statusService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EquipmentEvent::EQUIPMENT_TRANSFORM => [
                ['onEquipmentTransform', 1000], // change the status before original equipment is destroyed
            ],
            EquipmentEvent::EQUIPMENT_DESTROYED => [
                ['onEquipmentDestroyed'],
                ['onEquipmentRemovedFromInventory', -10],
            ],
            EquipmentEvent::EQUIPMENT_CREATED => [
                ['onNewEquipmentInInventory', -2000], // after the overflowing part has been solved
            ],
            EquipmentEvent::INVENTORY_OVERFLOW => [
                ['onEquipmentRemovedFromInventory'],
            ],
            EquipmentEvent::CHANGE_HOLDER => [
                ['onEquipmentRemovedFromInventory', 2000],
                ['onNewEquipmentInInventory', -2000],
            ],
        ];
    }

    public function onEquipmentTransform(TransformEquipmentEvent $event): void
    {
        $newEquipment = $event->getGameEquipment();
        $oldEquipment = $event->getEquipmentFrom();

        $statuses = $oldEquipment->getStatuses();
        if ($event->isFromCookAction() || ($event->isFromHyperfreezeAction() && $newEquipment->isAStandardRation())) {
            $statuses = $statuses->filter(static fn (Status $status) => $status->getName() !== EquipmentStatusEnum::CONTAMINATED);
        }

        /** @var Status $status */
        foreach ($statuses as $status) {
            $this->statusService->createStatusFromName(
                $status->getName(),
                $newEquipment,
                $event->getTags(),
                $event->getTime(),
            );
        }
    }

    public function onEquipmentDestroyed(EquipmentEvent $event): void
    {
        $equipment = $event->getGameEquipment();
        $this->statusService->removeAllStatuses($equipment, $event->getTags(), $event->getTime());

        if ($event->hasAllTags([GearItemEnum::INVERTEBRATE_SHELL, EventEnum::FIRE])) {
            $this->breakPlaceEquipment($event);
        }
    }

    public function onNewEquipmentInInventory(EquipmentEvent $event): void
    {
        $equipment = $event->getGameEquipment();
        $reasons = $event->getTags();
        $time = $event->getTime();
        $holder = $equipment->getHolder();

        if ($holder instanceof Player) {
            if ($equipment->hasStatus(EquipmentStatusEnum::HIDDEN)) {
                $this->statusService->removeStatus(EquipmentStatusEnum::HIDDEN, $equipment, $reasons, $time);
            } elseif (
                $equipment->hasStatus(EquipmentStatusEnum::HEAVY)
                && !$holder->hasStatus(PlayerStatusEnum::BURDENED)
            ) {
                $statusConfig = $this->statusService->getStatusConfigByNameAndDaedalus(PlayerStatusEnum::BURDENED, $holder->getDaedalus());
                $this->statusService->createStatusFromConfig($statusConfig, $holder, $reasons, $time);
            }
        }
    }

    public function onEquipmentRemovedFromInventory(EquipmentEvent $event): void
    {
        $equipment = $event->getGameEquipment();
        $reasons = $event->getTags();
        $time = $event->getTime();

        $player = $equipment->getHolder();
        if ($player instanceof Player
            && $player->hasStatus(PlayerStatusEnum::BURDENED)
            && $equipment->hasStatus(EquipmentStatusEnum::HEAVY)
            && $player->getEquipments()->filter(static function (GameItem $item) {
                return $item->hasStatus(EquipmentStatusEnum::HEAVY);
            })->count() >= 1
        ) {
            $this->statusService->removeStatus(PlayerStatusEnum::BURDENED, $player, $reasons, $time);
        }
    }

    private function breakPlaceEquipment(EquipmentEvent $event): void
    {
        $place = $event->getGameEquipment()->getPlace();

        $breakablePlaceEquipment = $place->getEquipments()->filter(static function (GameEquipment $equipment) {
            return $equipment->getEquipment()->isBreakable();
        });

        /** @var GameEquipment $equipment */
        foreach ($breakablePlaceEquipment as $equipment) {
            $this->statusService->createStatusFromName(
                statusName: EquipmentStatusEnum::BROKEN,
                holder: $equipment,
                tags: $event->getTags(),
                time: $event->getTime(),
                visibility: VisibilityEnum::PUBLIC,
            );
        }
    }
}
