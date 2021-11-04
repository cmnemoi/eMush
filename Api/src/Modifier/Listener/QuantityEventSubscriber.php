<?php

namespace Mush\Modifier\Listener;

use Mush\Game\Event\AbstractQuantityEvent;
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
            AbstractQuantityEvent::CHANGE_VARIABLE => ['onQuantityEvent', 100], //Applied before the modification is applied
        ];
    }

    public function onQuantityEvent(AbstractQuantityEvent $event): void
    {
        $initQuantity = $event->getQuantity();

        $event->setQuantity($this->modifierService->getEventModifiedValue(
            $event->getModifierHolder(),
            [AbstractQuantityEvent::CHANGE_VARIABLE],
            $event->getModifiedVariable(),
            $initQuantity,
            $event->getReason()
        ));
    }
}
