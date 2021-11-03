<?php

namespace Mush\Modifier\Listener;

use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Daedalus\Event\DaedalusModifierEvent;
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
            AbstractQuantityEvent::class => ['onQuantityEvent', 100],
        ];
    }

    public function onQuantityEvent(AbstractQuantityEvent $event): void
    {
        $initQuantity = $event->getQuantity();

        $event->setQuantity($this->modifierService->getEventModifiedValue(
            $event->getModifierHolder(),
            [DaedalusModifierEvent::CHANGE_HULL],
            DaedalusVariableEnum::HULL,
            $initQuantity,
            $event->getReason()
        ));
    }
}
