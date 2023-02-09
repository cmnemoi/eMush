<?php

namespace Mush\Modifier\Listener;

use Mush\Game\Event\VariableEventInterface;
use Mush\Modifier\Service\ModifierServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class QuantityEventSubscriber implements EventSubscriberInterface
{
    private ModifierServiceInterface $modifierService;

    public function __construct(
        ModifierServiceInterface $modifierService,
    ) {
        $this->modifierService = $modifierService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            VariableEventInterface::CHANGE_VARIABLE => ['onQuantityEvent', 100], // Applied before the modification is applied
        ];
    }

    public function onQuantityEvent(VariableEventInterface $event): void
    {
        $initQuantity = $event->getQuantity();

        $event->setQuantity($this->modifierService->getEventModifiedValue(
            $event->getModifierHolder(),
            [VariableEventInterface::CHANGE_VARIABLE],
            $event->getVariableName(),
            $initQuantity,
            $event->getTags(),
            $event->getTime()
        ));
    }
}
