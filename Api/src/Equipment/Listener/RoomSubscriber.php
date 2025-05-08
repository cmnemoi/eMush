<?php

declare(strict_types=1);

namespace Mush\Equipment\Listener;

use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Service\DeleteEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Place\Event\RoomEvent;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class RoomSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private DeleteEquipmentServiceInterface $deleteEquipment,
        private StatusServiceInterface $statusService,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            RoomEvent::ELECTRIC_ARC => 'onElectricArc',
            RoomEvent::DELETE_PLACE => 'onDeletePlace',
        ];
    }

    public function onElectricArc(RoomEvent $event): void
    {
        $room = $event->getPlace();

        if ($room->getType() !== PlaceTypeEnum::ROOM) {
            throw new \LogicException('place should be a room');
        }

        /** @var GameEquipment $equipment */
        foreach ($room->getEquipments() as $equipment) {
            if (!$equipment->isBroken()
                && !($equipment instanceof Door)
                && !($equipment instanceof GameItem)
                && $equipment->canBeDamaged()
            ) {
                $this->statusService->createStatusFromName(
                    EquipmentStatusEnum::BROKEN,
                    $equipment,
                    $event->getTags(),
                    $event->getTime(),
                    null,
                    VisibilityEnum::PUBLIC
                );
            }
        }
    }

    public function onDeletePlace(RoomEvent $event): void
    {
        foreach ($event->getPlace()->getEquipments() as $equipment) {
            $this->deleteEquipment->execute($equipment, tags: $event->getTags(), time: $event->getTime());
        }
    }
}
