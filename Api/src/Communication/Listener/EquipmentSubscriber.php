<?php

namespace Mush\Communication\Listener;

use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Communication\Services\NeronMessageServiceInterface;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EquipmentSubscriber implements EventSubscriberInterface
{
    private NeronMessageServiceInterface $neronMessageService;
    private GameEquipmentServiceInterface $gameEquipmentService;

    public function __construct(
        NeronMessageServiceInterface $neronMessageService,
        GameEquipmentServiceInterface $gameEquipmentService,
    ) {
        $this->neronMessageService = $neronMessageService;
        $this->gameEquipmentService = $gameEquipmentService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EquipmentEvent::EQUIPMENT_DESTROYED => 'onDestroyedEquipment',
        ];
    }

    public function onDestroyedEquipment(EquipmentEvent $event): void
    {
        $equipmentName = $event->getEquipment()->getName();

        if (in_array($equipmentName, [EquipmentEnum::SHOWER, EquipmentEnum::THALASSO])) {
            $holder = $event->getEquipment()->getHolder();
            $daedalus = $holder->getDaedalus();

            $numberShowersLeft = ($this->gameEquipmentService->findByNameAndDaedalus(EquipmentEnum::THALASSO, $daedalus)->count() +
                $this->gameEquipmentService->findByNameAndDaedalus(EquipmentEnum::SHOWER, $daedalus)->count());

            if ($numberShowersLeft <= 1) {
                $this->neronMessageService->createNeronMessage(
                    NeronMessageEnum::NO_SHOWER,
                    $daedalus,
                    $event->getLogParameters(),
                    $event->getTime(),
                );
            } else {
                $this->neronMessageService->createNeronMessage(
                    NeronMessageEnum::DISMANTLED_SHOWER,
                    $daedalus,
                    $event->getLogParameters(),
                    $event->getTime(),
                );
            }
        }
    }
}
