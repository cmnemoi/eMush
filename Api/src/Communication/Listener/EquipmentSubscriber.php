<?php

namespace Mush\Communication\Listener;

use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Communication\Services\NeronMessageServiceInterface;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Service\EquipmentFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EquipmentSubscriber implements EventSubscriberInterface
{
    private NeronMessageServiceInterface $neronMessageService;
    private EquipmentFactoryInterface $gameEquipmentService;

    public function __construct(
        NeronMessageServiceInterface $neronMessageService,
        EquipmentFactoryInterface    $gameEquipmentService,
    ) {
        $this->neronMessageService = $neronMessageService;
        $this->gameEquipmentService = $gameEquipmentService;
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
