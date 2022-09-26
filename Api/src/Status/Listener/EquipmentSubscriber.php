<?php

namespace Mush\Status\Listener;

use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\TransformEquipmentEvent;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EquipmentSubscriber implements EventSubscriberInterface
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
            EquipmentEvent::EQUIPMENT_TRANSFORM => ['onEquipmentTransform', 1000], // change the status before original equipment is destroyed
            EquipmentEvent::EQUIPMENT_DESTROYED => [['onEquipmentDestroyed'], ['onEquipmentRemovedFromInventory', -10]],
            EquipmentEvent::EQUIPMENT_CREATED => ['onNewEquipmentInInventory', -2000], // after the overflowing part has been solved
            EquipmentEvent::CHANGE_HOLDER => [['onEquipmentRemovedFromInventory', 2000], ['onNewEquipmentInInventory', -2000]],
        ];
    }

    public function onEquipmentTransform(TransformEquipmentEvent $event): void
    {
        $newEquipment = $event->getEquipment();
        $oldEquipment = $event->getEquipmentFrom();

        /** @var Status $status */
        foreach ($oldEquipment->getStatuses() as $status) {
            $newEquipment->addStatus($status);
            $this->statusService->persist($status);
        }
    }

    public function onEquipmentDestroyed(EquipmentEvent $event): void
    {
        $equipment = $event->getExistingEquipment();

        if ($equipment === null) {
            throw new \LogicException('Replaced equipment should be provided');
        }

        $this->statusService->removeAllStatuses($equipment, $event->getReason(), $event->getTime());
    }

    public function onNewEquipmentInInventory(EquipmentEvent $event): void
    {
        $equipment = $event->getExistingEquipment() ?: $event->getNewEquipment();
        $reason = $event->getReason();
        $time = $event->getTime();

        if ($equipment === null) {
            throw new \LogicException('Equipment should be provided');
        }

        $holder = $equipment->getHolder();
        if ($holder instanceof Player) {
            if ($equipment->hasStatus(EquipmentStatusEnum::HIDDEN)) {
                $this->statusService->removeStatus(EquipmentStatusEnum::HIDDEN, $equipment, $reason, $time);
            } elseif (
                $equipment->hasStatus(EquipmentStatusEnum::HEAVY) &&
                !$holder->hasStatus(PlayerStatusEnum::BURDENED)
            ) {
                $statusConfig = $this->statusService->getStatusConfigByNameAndDaedalus(PlayerStatusEnum::BURDENED, $holder->getDaedalus());
                $this->statusService->createStatusFromConfig($statusConfig, $holder, $reason, $time);
            }
        }
    }

    public function onEquipmentRemovedFromInventory(EquipmentEvent $event): void
    {
        $equipment = $event->getExistingEquipment();
        $reason = $event->getReason();
        $time = $event->getTime();

        if ($equipment === null) {
            throw new \LogicException('Existing equipment should be provided');
        }

        $player = $equipment->getHolder();
        if ($player instanceof Player &&
            $player->hasStatus(PlayerStatusEnum::BURDENED) &&
            $player->getEquipments()->filter(function (GameItem $item) {
                return $item->hasStatus(EquipmentStatusEnum::HEAVY);
            })->count() === 1
        ) {
            $this->statusService->removeStatus(PlayerStatusEnum::BURDENED, $player, $reason, $time);
        }
    }
}
