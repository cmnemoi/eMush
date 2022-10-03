<?php

namespace Mush\Game\Service;

use Mush\Game\Event\AbstractGameEvent;
use Mush\Game\Event\AbstractModifierHolderEvent;
use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Modifier\Service\ModifierListenerServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EventService implements EventServiceInterface
{
    private EventDispatcherInterface $eventDispatcher;
    private ModifierListenerServiceInterface $modifierListenerService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ModifierListenerServiceInterface $modifierListenerService
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->modifierListenerService = $modifierListenerService;
    }

    public function callEvent(AbstractGameEvent $event, string $name, AbstractGameEvent $caller = null): void
    {
        if ($caller !== null) {
            $event->setReason(array_merge($event->getReasons(), $caller->getReasons()));
        }
        $event->setEventName($name);

        $handled = false;
        if ($event instanceof AbstractModifierHolderEvent) {
            $handled = $this->modifierListenerService->applyModifiers($event);
        }

        $this->eventDispatcher->dispatch($event, $name);

        if (!$event instanceof AbstractModifierHolderEvent) {
            return;
        }

        if (!$handled) {
            $holder = $event->getModifierHolder();
            if (!$holder instanceof Player) {
                return;
            }

            foreach (PlayerVariableEnum::getInteractivePlayerVariables() as $variable) {
                $variableEvent = new PlayerVariableEvent(
                    $holder,
                    $variable,
                    0,
                    $event->getEventName(),
                    new \DateTime()
                );
                $variableEvent->setArtificial(true);
                $variableEvent->setModified(false);

                $this->callEvent($variableEvent, AbstractQuantityEvent::CHANGE_VARIABLE, $event);
            }
        }
    }
}
