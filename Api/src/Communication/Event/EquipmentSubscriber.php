<?php

namespace Mush\Communication\Event;

use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Communication\Services\MessageServiceInterface;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\RoomLog\Enum\VisibilityEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EquipmentSubscriber implements EventSubscriberInterface
{
    private MessageServiceInterface $messageService;

    public function __construct(
        MessageServiceInterface $messageService
    ) {
        $this->messageService = $messageService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EquipmentEvent::EQUIPMENT_BROKEN => 'onBrokenEquipment',
        ];
    }

    public function onBrokenEquipment(EquipmentEvent $event): void
    {
        if ($event->getVisibility() === VisibilityEnum::PUBLIC) {
            $equipment = $event->getEquipment();
            $equipmentName = $equipment->getName();

            $daedalus = $equipment->getCurrentPlace()->getDaedalus();

            switch ($equipmentName) {
                case EquipmentEnum::OXYGEN_TANK:
                    $message = NeronMessageEnum::BROKEN_OXYGEN;
                    break;
                case EquipmentEnum::FUEL_TANK:
                    $message = NeronMessageEnum::BROKEN_FUEL;
                    break;
                default:
                    $message = NeronMessageEnum::BROKEN_EQUIPMENT;
                    break;
            }

            $parentMessage = $this->messageService->getMessageNeronCycleFailures($daedalus);

            if ($equipment instanceof GameItem) {
                $this->messageService->createNeronMessage($message, $daedalus, ['targetItem' => $equipmentName], $event->getTime(), $parentMessage);
            } elseif (!($equipment instanceof Door)) {
                $this->messageService->createNeronMessage($message, $daedalus, ['targetEquipment' => $equipmentName], $event->getTime(), $parentMessage);
            }
        }
    }
}
