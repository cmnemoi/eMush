<?php

namespace Mush\Equipment\Listener;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EquipmentSubscriber implements EventSubscriberInterface
{
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    public function __construct(
        GameEquipmentServiceInterface $gameEquipmentService,
        StatusServiceInterface $statusService,
    ) {
        $this->gameEquipmentService = $gameEquipmentService;
        $this->statusService = $statusService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EquipmentEvent::EQUIPMENT_CREATED => 'onEquipmentCreated',
            EquipmentEvent::EQUIPMENT_FIXED => 'onEquipmentFixed',
            EquipmentEvent::EQUIPMENT_BROKEN => 'onEquipmentBroken',
            EquipmentEvent::EQUIPMENT_DESTROYED => 'onEquipmentDestroyed',
            EquipmentEvent::EQUIPMENT_TRANSFORM => 'onEquipmentTransform',
        ];
    }

    public function onEquipmentCreated(EquipmentEvent $event): void
    {
        $player = $event->getPlayer();
        $equipment = $event->getEquipment();

        if ($player === null) {
            throw new \LogicException('Player should be provided');
        }

        if (!$equipment instanceof GameItem || $player->getItems()->count() >= $this->getGameConfig($equipment)->getMaxItemInInventory()) {
            $equipment->setPlace($player->getPlace());
        } else {
            $equipment->setPlayer($player);
        }

        $this->gameEquipmentService->persist($equipment);
    }

    public function onEquipmentFixed(EquipmentEvent $event): void
    {
        $equipment = $event->getEquipment();

        if (($brokenStatus = $equipment->getStatusByName(EquipmentStatusEnum::BROKEN)) === null) {
            throw new \LogicException('equipment should be broken to be fixed');
        }

        $equipment->removeStatus($brokenStatus);
        $this->gameEquipmentService->persist($equipment);
    }

    public function onEquipmentBroken(EquipmentEvent $event): void
    {
        $equipment = $event->getEquipment();

        $this->statusService->createCoreStatus(EquipmentStatusEnum::BROKEN, $equipment);
        $this->gameEquipmentService->persist($equipment);
    }

    public function onEquipmentDestroyed(EquipmentEvent $event): void
    {
        $equipment = $event->getEquipment();
        $equipment->removeLocation();

        $this->gameEquipmentService->delete($equipment);
    }

    public function onEquipmentTransform(EquipmentEvent $event): void
    {
        $equipment = $event->getEquipment();
        $place = $equipment->getCurrentPlace();
        $player = $event->getPlayer();

        if (($newEquipment = $event->getReplacementEquipment()) === null) {
            throw new \LogicException('Replacement equipment should be provided');
        }

        /** @var Status $status */
        foreach ($equipment->getStatuses() as $status) {
            $newEquipment->addStatus($status);
            $this->statusService->persist($status);
        }

        if ($newEquipment instanceof GameItem && $player !== null && $player->getItems()->count() < $this->getGameConfig($equipment)->getMaxItemInInventory()) {
            $newEquipment->setPlayer($player);
        } else {
            $newEquipment->setPlace($place);
        }

        $this->gameEquipmentService->delete($equipment);
        $this->gameEquipmentService->persist($newEquipment);
    }

    private function getGameConfig(GameEquipment $gameEquipment): GameConfig
    {
        return $gameEquipment->getEquipment()->getGameConfig();
    }
}
