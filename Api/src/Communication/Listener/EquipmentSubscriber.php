<?php

namespace Mush\Communication\Listener;

use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Communication\Services\NeronMessageServiceInterface;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Service\EquipmentFactoryInterface;
use Mush\Equipment\Service\EquipmentServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EquipmentSubscriber implements EventSubscriberInterface
{
    private NeronMessageServiceInterface $neronMessageService;
    private EquipmentServiceInterface $equipmentService;

    public function __construct(
        NeronMessageServiceInterface $neronMessageService,
        EquipmentServiceInterface    $equipmentService,
    ) {
        $this->neronMessageService = $neronMessageService;
        $this->equipmentService = $equipmentService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EquipmentEvent::EQUIPMENT_DESTROYED => [
                ['onEquipmentDestroyed'],
            ],
        ];
    }

    public function onEquipmentDestroyed(EquipmentEvent $event): void
    {
        $equipmentName = $event->getEquipment()->getName();

        if (in_array($equipmentName, [EquipmentEnum::SHOWER, EquipmentEnum::THALASSO])) {
            $holder = $event->getEquipment()->getHolder();

            if ($holder === null) {
                throw new \LogicException('no Daedalus found for the destroyed item');
            }

            $daedalus = $holder->getPlace()->getDaedalus();

            $numberShowersLeft = ($this->equipmentService->findByNameAndDaedalus(EquipmentEnum::THALASSO, $daedalus)->count() +
                $this->equipmentService->findByNameAndDaedalus(EquipmentEnum::SHOWER, $daedalus)->count());

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
