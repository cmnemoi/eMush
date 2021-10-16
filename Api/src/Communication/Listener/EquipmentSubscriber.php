<?php

namespace Mush\Communication\Listener;

use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Communication\Services\NeronMessageServiceInterface;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Event\EquipmentEventInterface;
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
            EquipmentEventInterface::EQUIPMENT_BROKEN => 'onBrokenEquipment',
            EquipmentEventInterface::EQUIPMENT_DESTROYED => 'onDestroyedEquipment',
        ];
    }

    public function onBrokenEquipment(EquipmentEventInterface $event): void
    {
        $this->neronMessageService->createBrokenEquipmentMessage($event->getEquipment(), $event->getVisibility(), $event->getTime());
    }

    public function onDestroyedEquipment(EquipmentEventInterface $event): void
    {
        $equipment = $event->getEquipment();

        if (in_array($equipment->getName(), [EquipmentEnum::SHOWER, EquipmentEnum::THALASSO])) {
            $player = $event->getPlayer();

            if ($player === null) {
                throw new \LogicException('there should be a player in this event');
            }
            $daedalus = $player->getDaedalus();

            $numberShowerLeft = ($this->gameEquipmentService->findByNameAndDaedalus(EquipmentEnum::THALASSO, $daedalus)->count() +
                $this->gameEquipmentService->findByNameAndDaedalus(EquipmentEnum::SHOWER, $daedalus)->count());

            if ($numberShowerLeft === 0) {
                $this->neronMessageService->createNeronMessage(
                    NeronMessageEnum::NO_SHOWER,
                    $daedalus,
                    ['character' => $player->getLogName()],
                    $event->getTime(),
                );
            } else {
                $this->neronMessageService->createNeronMessage(
                    NeronMessageEnum::DISMANTLED_SHOWER,
                    $daedalus,
                    ['character' => $player->getLogName()],
                    $event->getTime(),
                );
            }
        }
    }
}
