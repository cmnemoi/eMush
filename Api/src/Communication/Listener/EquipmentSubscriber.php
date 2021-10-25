<?php

namespace Mush\Communication\Listener;

use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Communication\Services\NeronMessageServiceInterface;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Player\Entity\Player;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

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
        $equipmentName = $event->getEquipmentName();

        if (in_array($equipmentName, [EquipmentEnum::SHOWER, EquipmentEnum::THALASSO])) {
            $holder = $event->getHolder();

            if ($holder instanceof Player) {
                throw new UnexpectedTypeException($holder, Player::class);
            }
            $daedalus = $holder->getPlace()->getDaedalus();

            $numberShowerLeft = ($this->gameEquipmentService->findByNameAndDaedalus(EquipmentEnum::THALASSO, $daedalus)->count() +
                $this->gameEquipmentService->findByNameAndDaedalus(EquipmentEnum::SHOWER, $daedalus)->count());

            if ($numberShowerLeft === 0) {
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
