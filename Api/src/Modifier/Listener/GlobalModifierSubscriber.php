<?php

namespace Mush\Modifier\Listener;

use Mush\Game\Event\AbstractModifierHolderEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Modifier\Entity\Modifier;
use Mush\Modifier\Event\ModifierEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GlobalModifierSubscriber implements EventSubscriberInterface
{

    private EventServiceInterface $eventService;

    public function __construct(EventServiceInterface $eventService)
    {
        $this->eventService = $eventService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AbstractModifierHolderEvent::class => ['onModifierHolderEvent', 100_000]
        ];
    }

    public function onModifierHolderEvent(AbstractModifierHolderEvent $event) {
        $reasonName = $event->getReason();
        $eventName  = $event->getEventName();
        $holder = $event->getModifierHolder();

        $modifiersToApply = $holder->getModifiersAtReach()->filter(fn (Modifier $modifier) =>
            $modifier->getConfig()->isTargetedBy($eventName, $reasonName)
        );

        /* @var Modifier $modifier */
        foreach ($modifiersToApply as $modifier) {
            $modifier->getConfig()->modify($event, $this->eventService);
        };
    }

}
