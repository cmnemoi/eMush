<?php

namespace Mush\Modifier\ModifierHandler;

use Mush\Game\Entity\Collection\EventChain;
use Mush\Game\Entity\VariableEventConfig;
use Mush\Modifier\Entity\Config\TriggerEventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Modifier\Entity\ModifierProviderInterface;
use Mush\Modifier\Enum\ModifierStrategyEnum;
use Mush\Modifier\Service\EventCreationServiceInterface;
use Mush\Player\Event\PlayerCycleEvent;

class AddEvent extends AbstractModifierHandler
{
    protected string $name = ModifierStrategyEnum::ADD_EVENT;

    private EventCreationServiceInterface $eventCreationService;

    public function __construct(
        EventCreationServiceInterface $eventCreationService,
    ) {
        $this->eventCreationService = $eventCreationService;
    }

    public function handleEventModifier(
        GameModifier $modifier,
        EventChain $events,
        string $eventName,
        array $tags,
        \DateTime $time
    ): EventChain {
        /** @var TriggerEventModifierConfig $modifierConfig */
        $modifierConfig = $modifier->getModifierConfig();
        $eventConfig = $modifierConfig->getTriggeredEvent();

        $priority = $modifierConfig->getPriorityAsInteger();
        if ($priority === 0) {
            throw new \Exception('Modifier cannot have a priority of 0 (restricted to the initialEvent)');
        }

        $tags[] = $modifier->getModifierConfig()->getModifierName() ?: $modifier->getModifierConfig()->getName();

        $newEvents = new EventChain([]);
        if ($eventConfig instanceof VariableEventConfig) {
            $initialEvent = $events->getInitialEvent();
            $player = $initialEvent instanceof PlayerCycleEvent ? $initialEvent->getPlayer() : $initialEvent?->getAuthor();

            if ($player !== null && $modifierConfig->getApplyWhenTargeted()) {
                $modifierTarget = $player;
            } else {
                $modifierTarget = $modifier->getModifierHolder();
            }

            $newEvents = $this->createVariableEvents(
                $modifierTarget,
                $eventConfig,
                $modifierConfig,
                $modifier->getModifierProvider(),
                $priority,
                $tags,
                $time
            );
        }

        if ($newEvents->count() > 0) {
            $events = $events->addEvents($newEvents);

            return $this->addModifierEvent($events, $modifier, $tags, $time);
        }

        return $events;
    }

    private function createVariableEvents(
        ModifierHolderInterface $modifierHolder,
        VariableEventConfig $eventConfig,
        TriggerEventModifierConfig $modifierConfig,
        ModifierProviderInterface $modifierProvider,
        int $priority,
        array $tags,
        \DateTime $time
    ): EventChain {
        $events = [];

        $eventTargets = $this->eventCreationService->getEventTargetsFromModifierHolder(
            $eventConfig,
            $modifierConfig->getEventTargetRequirements(),
            $modifierConfig->getTargetFilters(),
            $modifierHolder,
            $modifierProvider
        );

        foreach ($eventTargets as $target) {
            $event = $eventConfig->createEvent($priority, $tags, $time, $target);
            if ($event !== null) {
                $events[] = $event;
            }
        }

        return new EventChain($events);
    }
}
