<?php

namespace Mush\Chat\Listener;

use Mush\Chat\Enum\NeronMessageEnum;
use Mush\Chat\Services\NeronMessageServiceInterface;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
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
            EquipmentEvent::EQUIPMENT_DESTROYED => [
                ['onEquipmentDestroyed'],
            ],
        ];
    }

    public function onEquipmentDestroyed(EquipmentEvent $event): void
    {
        $equipment = $event->getGameEquipment();
        $equipmentName = $equipment->getName();
        $holder = $equipment->getHolder();
        $daedalus = $holder->getPlace()->getDaedalus();

        if (\in_array($equipmentName, [EquipmentEnum::SHOWER, EquipmentEnum::THALASSO], true)) {
            $numberShowersLeft = ($this->gameEquipmentService->findEquipmentsByNameAndDaedalus(EquipmentEnum::THALASSO, $daedalus)->count() +
                $this->gameEquipmentService->findEquipmentsByNameAndDaedalus(EquipmentEnum::SHOWER, $daedalus)->count());

            $this->neronMessageService->createNeronMessage(
                messageKey: $numberShowersLeft <= 1 ? NeronMessageEnum::NO_SHOWER : NeronMessageEnum::DISMANTLED_SHOWER,
                daedalus: $daedalus,
                parameters: $event->getLogParameters(),
                dateTime: $event->getTime(),
            );
        }

        if ($equipmentName === ItemEnum::SCHRODINGER) {
            $this->neronMessageService->createNeronMessage(
                messageKey: NeronMessageEnum::SCHRODINGER_DEATH,
                daedalus: $daedalus,
                parameters: $event->getLogParameters(),
                dateTime: $event->getTime(),
            );
        }
    }
}
