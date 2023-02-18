<?php

namespace Mush\Modifier\Listener;

use Mush\Disease\Event\DiseaseEvent;
use Mush\Modifier\Service\ModifierListenerService\DiseaseModifierServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DiseaseSubscriber implements EventSubscriberInterface
{
    private DiseaseModifierServiceInterface $diseaseModifierService;

    public function __construct(
        DiseaseModifierServiceInterface $diseaseModifierService,
    ) {
        $this->diseaseModifierService = $diseaseModifierService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DiseaseEvent::APPEAR_DISEASE => 'onDiseaseAppear',
            DiseaseEvent::CURE_DISEASE => 'onDiseaseCured',
        ];
    }

    public function onDiseaseAppear(DiseaseEvent $event): void
    {
        $this->diseaseModifierService->newDisease($event->getPlayer(), $event->getDiseaseConfig(), $event->getTags(), $event->getTime());
    }

    public function onDiseaseCured(DiseaseEvent $event): void
    {
        $this->diseaseModifierService->cureDisease($event->getPlayer(), $event->getDiseaseConfig(), $event->getTags(), $event->getTime());
    }
}
