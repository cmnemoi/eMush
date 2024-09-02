<?php

namespace Mush\Communication\Listener;

use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Communication\Services\NeronMessageServiceInterface;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Status\Enum\PlaceStatusEnum;
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
            EquipmentEvent::EQUIPMENT_DESTROYED => [
                ['onEquipmentDestroyed'],
            ],
        ];
    }

    public function onEquipmentDestroyed(EquipmentEvent $event): void
    {
        $equipment = $event->getGameEquipment();
        $equipmentName = $equipment->getName();

        if ($equipment->getPlace()->hasStatus(PlaceStatusEnum::DELOGGED->toString())) {
            return;
        }

        if (\in_array($equipmentName, [EquipmentEnum::SHOWER, EquipmentEnum::THALASSO], true)) {
            $holder = $equipment->getHolder();

            $daedalus = $holder->getPlace()->getDaedalus();

            $numberShowersLeft = ($this->gameEquipmentService->findEquipmentByNameAndDaedalus(EquipmentEnum::THALASSO, $daedalus)->count() +
                $this->gameEquipmentService->findEquipmentByNameAndDaedalus(EquipmentEnum::SHOWER, $daedalus)->count());

            $this->neronMessageService->createNeronMessage(
                messageKey: $numberShowersLeft <= 1 ? NeronMessageEnum::NO_SHOWER : NeronMessageEnum::DISMANTLED_SHOWER,
                daedalus: $daedalus,
                parameters: $event->getLogParameters(),
                dateTime: $event->getTime(),
            );
        }
    }
}
