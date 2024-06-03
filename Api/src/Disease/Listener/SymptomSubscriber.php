<?php

namespace Mush\Disease\Listener;

use Mush\Disease\Event\SymptomEvent;
use Mush\Disease\Service\SymptomHandlerServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SymptomSubscriber implements EventSubscriberInterface
{
    private SymptomHandlerServiceInterface $symptomHandlerService;

    public function __construct(
        SymptomHandlerServiceInterface $symptomHandlerService,
    ) {
        $this->symptomHandlerService = $symptomHandlerService;
    }

    public static function getSubscribedEvents()
    {
        return [
            SymptomEvent::TRIGGER_SYMPTOM => 'onTriggerSymptom',
        ];
    }

    public function onTriggerSymptom(SymptomEvent $event): void
    {
        $player = $event->getTargetPlayer();
        if ($player->isDead()) {
            return;
        }

        $symptomHandler = $this->symptomHandlerService->getSymptomHandler(
            $event->getSymptomName()
        );

        // some symptoms are only a message, there is no handler for those
        if ($symptomHandler === null) {
            return;
        }

        $symptomHandler->applyEffects(
            $event->getTargetPlayer(),
            $event->getPriority(),
            $event->getTags(),
            $event->getTime()
        );
    }
}
