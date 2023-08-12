<?php

namespace Mush\Disease\Listener;

use Mush\Disease\Event\SymptomEvent;
use Mush\Disease\Service\SymptomHandlerService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SymptomSubscriber implements EventSubscriberInterface
{
    private SymptomHandlerService $symptomHandlerService;

    public function __construct(
        SymptomHandlerService $symptomHandlerService,
    ) {
        $this->symptomHandlerService = $symptomHandlerService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SymptomEvent::TRIGGER_SYMPTOM => 'onTriggerSymptom',
        ];
    }

    public function onTriggerSymptom(SymptomEvent $event): void
    {
        $symptomName = $event->getSymptomName();
        $player = $event->getAuthor();
        $time = $event->getTime();

        $symptomHandler = $this->symptomHandlerService->getSymptomHandler($symptomName);

        if ($symptomHandler !== null) {
            $symptomHandler->applyEffects($symptomName, $player, $time);
        }
    }
}
