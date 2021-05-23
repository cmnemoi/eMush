<?php

namespace Mush\Alert\Listener;

use Mush\Alert\Entity\Alert;
use Mush\Alert\Enum\AlertEnum;
use Mush\Alert\Service\AlertServiceInterface;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EquipmentSubscriber implements EventSubscriberInterface
{
    private AlertServiceInterface $alertService;

    public function __construct(
        AlertServiceInterface $alertService
    ) {
        $this->alertService = $alertService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EquipmentEvent::EQUIPMENT_FIXED => 'onEquipmentFixed',
            EquipmentEvent::EQUIPMENT_BROKEN => 'onEquipmentBroken',
        ];
    }

    public function onEquipmentBroken(EquipmentEvent $event): void
    {
        $equipment = $event->getEquipment();

        if ($equipment->getName() === EquipmentEnum::GRAVITY_SIMULATOR) {
            $gravityAlert = new Alert();
            $gravityAlert
                ->setDaedalus($equipment->getCurrentPlace()->getDaedalus())
                ->setName(AlertEnum::NO_GRAVITY)
            ;

            $this->alertService->persist($gravityAlert);
        }
    }

    public function onEquipmentFixed(EquipmentEvent $event): void
    {
        $equipment = $event->getEquipment();

        if ($equipment->getName() === EquipmentEnum::GRAVITY_SIMULATOR) {
            $gravitySituation = $this->alertService->findByNameAndDaedalus(AlertEnum::NO_GRAVITY, $equipment->getCurrentPlace()->getDaedalus());

            if ($gravitySituation === null) {
                throw new \LogicException('there should be a gravitySituation on this Daedalus');
            }

            $this->alertService->delete($gravitySituation);
        }
    }
}
