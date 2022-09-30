<?php

namespace Mush\Modifier\Listener;

use Mush\Game\Event\AbstractGameEvent;
use Mush\Modifier\Entity\Modifier;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Player\Event\PlayerVariableEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GlobalModifierSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents(): array
    {
        return [
            AbstractGameEvent::class => 'onEvent'
        ];
    }

    public function onEvent(AbstractGameEvent $event) {
        $reasonName = $event->getReason();
        $eventName  = $event->getEvent();
        $holder = $this->getModifierHolder($event);

        $modifiersToApply = $holder->getAllModifiers()->filter(function (Modifier $modifier) use ($eventName, $reasonName) {
            return $modifier->getConfig()->isTargetedBy($eventName, $reasonName);
        });

        /* @var Modifier $modifier */
        foreach ($modifiersToApply as $modifier) {
            $modifier->getConfig()->modify($event);
        };
    }

    private function getModifierHolder(AbstractGameEvent $event) : ModifierHolder | null {
        if ($event instanceof PlayerVariableEvent) {
            return $event->getPlayer();
        }

        // @TODO

        return null;
    }

}
