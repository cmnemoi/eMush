<?php

namespace Mush\Equipment\Listener;

use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\EquipmentInitEvent;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Equipment\Event\MoveEquipmentEvent;
use Mush\Equipment\Event\TransformEquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EquipmentSubscriber implements EventSubscriberInterface
{
    private GameEquipmentServiceInterface $gameEquipmentService;
    private EventServiceInterface $eventService;

    public function __construct(
        GameEquipmentServiceInterface $gameEquipmentService,
        EventServiceInterface $eventService
    ) {
        $this->gameEquipmentService = $gameEquipmentService;
        $this->eventService = $eventService;
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
                ['initModifier', 1000],
                ['checkInventoryOverflow', -1001],
                ['onEquipmentDestroyed', -1000], // the equipment is deleted after every other effect has been applied
            ],
            EquipmentEvent::CHANGE_HOLDER => [
                ['onChangeHolder', -100], // the equipment is deleted after every other effect has been applied
            ],
        ];
    }

    public function initModifier(EquipmentEvent $event): void
    {
        $equipment = $event->getGameEquipment();
        $config = $equipment->getEquipment();
        $reasons = $event->getTags();
        $time = $event->getTime();

        $equipmentEvent = new EquipmentInitEvent(
            $equipment,
            $config,
            $reasons,
            $time
        );

        $this->eventService->callEvent($equipmentEvent, EquipmentInitEvent::NEW_EQUIPMENT);
    }

    public function onEquipmentDestroyed(EquipmentEvent $event): void
    {
        $this->eventService->callEvent($event, EquipmentEvent::EQUIPMENT_DELETE);
    }

    public function onEquipmentDelete(EquipmentEvent $event): void
    {
        if ($event instanceof TransformEquipmentEvent) {
            $equipment = $event->getEquipmentFrom();
        } else {
            $equipment = $event->getGameEquipment();
        }

        $this->gameEquipmentService->delete($equipment);
    }

    public function onInventoryOverflow(EquipmentEvent $event): void
    {
        $equipment = $event->getGameEquipment();
        $equipment->setHolder($equipment->getPlace());
        $this->gameEquipmentService->persist($equipment);
    }

    public function checkInventoryOverflow(EquipmentEvent $event): void
    {
        $equipment = $event->getGameEquipment();
        $holder = $equipment->getHolder();

        if ($holder instanceof Player
            && $holder->getEquipments()->count() > $holder->getPlayerInfo()->getCharacterConfig()->getMaxItemInInventory()
        ) {
            $equipmentEvent = new InteractWithEquipmentEvent(
                $equipment,
                $holder,
                VisibilityEnum::HIDDEN,
                $event->getTags(),
                new \DateTime()
            );

            $this->eventService->callEvent($equipmentEvent, EquipmentEvent::INVENTORY_OVERFLOW);
        }
    }

    public function onChangeHolder(MoveEquipmentEvent $event): void
    {
        $equipment = $event->getGameEquipment();
        $newHolder = $event->getNewHolder();

        $equipment->setHolder($newHolder);

        $this->gameEquipmentService->persist($equipment);
    }
}
