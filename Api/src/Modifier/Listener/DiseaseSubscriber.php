<?php

namespace Mush\Modifier\Listener;

use Mush\Disease\Event\DiseaseEvent;
use Mush\Modifier\Service\DiseaseModifierServiceInterface;
use Mush\Modifier\Service\ModifierService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DiseaseSubscriber implements EventSubscriberInterface
{
    private DiseaseModifierServiceInterface $diseaseModifierService;
    private ModifierService $modifierService;

    public function __construct(
        DiseaseModifierServiceInterface $diseaseModifierService,
        ModifierService $modifierService
    ) {
        $this->diseaseModifierService = $diseaseModifierService;
        $this->modifierService = $modifierService;
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
        $this->diseaseModifierService->newDisease($event->getPlayer(), $event->getDiseaseConfig());
    }

    public function onDiseaseCured(DiseaseEvent $event): void
    {
        $this->diseaseModifierService->cureDisease($event->getPlayer(), $event->getDiseaseConfig());
    }
}
