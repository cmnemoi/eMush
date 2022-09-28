<?php

namespace Mush\Equipment\Listener;

use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\EquipmentInitEvent;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Equipment\Event\TransformEquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Player\Entity\Player;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EquipmentSubscriber implements EventSubscriberInterface
{
    private GameEquipmentServiceInterface $gameEquipmentService;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        GameEquipmentServiceInterface $gameEquipmentService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->gameEquipmentService = $gameEquipmentService;
        $this->eventDispatcher = $eventDispatcher;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EquipmentEvent::EQUIPMENT_CREATED => [
                ['initModifier', 1000],
                ['checkInventoryOverflow'],
            ],
            EquipmentEvent::INVENTORY_OVERFLOW => [
                ['onInventoryOverflow', -1000],
            ],
            EquipmentEvent::EQUIPMENT_DESTROYED => [
                ['onEquipmentDestroyed', -1000], // the equipment is deleted after every other effect has been applied
            ],
            EquipmentEvent::EQUIPMENT_DELETE => [
                ['onEquipmentDelete'],
            ],
            EquipmentEvent::EQUIPMENT_TRANSFORM => [
                ['checkInventoryOverflow', -1001],
                ['onEquipmentDestroyed', -1000], // the equipment is deleted after every other effect has been applied
            ],
            EquipmentEvent::CHANGE_HOLDER => [
                ['onChangeHolder', -100], // the equipment is deleted after every other effect has been applied
            ],
        ];
    }

    public function initModifier(EquipmentEvent $event)
    {
        $equipment = $event->getEquipment();
        $config = $equipment->getEquipment();
        $reason = $event->getReason();
        $time = $event->getTime();

        $equipmentEvent = new EquipmentInitEvent(
            $equipment,
            $config,
            $reason,
            $time
        );

        $this->eventDispatcher->dispatch($equipmentEvent, EquipmentInitEvent::NEW_EQUIPMENT);
    }

    public function onEquipmentDestroyed(EquipmentEvent $event): void
    {
        $this->eventDispatcher->dispatch($event, EquipmentEvent::EQUIPMENT_DELETE);
    }

    public function onEquipmentDelete(EquipmentEvent $event): void
    {
        if ($event instanceof TransformEquipmentEvent) {
            $equipment = $event->getEquipmentFrom();
        } else {
            $equipment = $event->getEquipment();
        }

        $equipment->setHolder(null);
        $this->gameEquipmentService->delete($equipment);
    }

    public function onInventoryOverflow(EquipmentEvent $event): void
    {
        $equipment = $event->getEquipment();
        $equipment->setHolder($equipment->getPlace());
        $this->gameEquipmentService->persist($equipment);
    }

    public function checkInventoryOverflow(EquipmentEvent $event)
    {
        $equipment = $event->getEquipment();
        $holder = $equipment->getHolder();
        $gameConfig = $holder->getPlace()->getDaedalus()->getGameConfig();

        if ($holder instanceof Player && $holder->getEquipments()->count() > $gameConfig->getMaxItemInInventory()) {
            $equipmentEvent = new EquipmentEvent(
                $equipment,
                false,
                VisibilityEnum::HIDDEN,
                EquipmentEvent::INVENTORY_OVERFLOW,
                new \DateTime()
            );

            $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::INVENTORY_OVERFLOW);
        }
    }

    public function onChangeHolder(InteractWithEquipmentEvent $event): void
    {
        $equipment = $event->getEquipment();
        $holder = $equipment->getHolder();

        if ($holder instanceof Player) {
            $equipment->setHolder($holder->getPlace());
        } else {
            $equipment->setHolder($event->getActor());
        }

        $this->gameEquipmentService->persist($equipment);
    }
}
