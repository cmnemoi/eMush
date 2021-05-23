<?php

namespace Mush\Situation\Listener;

use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Situation\Entity\Situation;
use Mush\Situation\Enum\SituationEnum;
use Mush\Situation\Service\SituationServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EquipmentSubscriber implements EventSubscriberInterface
{
    private SituationServiceInterface $situationService;

    public function __construct(
        SituationServiceInterface $situationService
    ) {
        $this->situationService = $situationService;
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
            $gravitySituation = new Situation($equipment->getCurrentPlace()->getDaedalus(), SituationEnum::NO_GRAVITY, true);
            $this->situationService->persist($gravitySituation);
        }
    }

    public function onEquipmentFixed(EquipmentEvent $event): void
    {
        $equipment = $event->getEquipment();

        if ($equipment->getName() === EquipmentEnum::GRAVITY_SIMULATOR) {
            $gravitySituation = $this->situationService->findByNameAndDaedalus(SituationEnum::NO_GRAVITY, $equipment->getCurrentPlace()->getDaedalus());

            if ($gravitySituation === null) {
                throw new \LogicException('there should be a gravitySituation on this Daedalus');
            }

            $this->situationService->delete($gravitySituation);
        }
    }
}
