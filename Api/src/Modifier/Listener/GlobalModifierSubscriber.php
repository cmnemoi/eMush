<?php

namespace Mush\Modifier\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Modifier\Entity\Modifier;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Modifier\Service\EquipmentModifierServiceInterface;
use Mush\Modifier\Service\ModifierService;
use Mush\Player\Event\PlayerVariableEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GlobalModifierSubscriber implements EventSubscriberInterface
{
    private ModifierService $modifierService;

    public function __construct(
        ModifierService $modifierService
    ) {
        $this->modifierService = $modifierService;
    }

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
            return $modifier->isTargetedBy($eventName, $reasonName);
        });

        foreach ($modifiersToApply as $modifier) {
            $modifier->modify($event);
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
