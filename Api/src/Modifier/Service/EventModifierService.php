<?php

namespace Mush\Modifier\Service;

use Mush\Action\Enum\ActionVariableEnum;
use Mush\Action\Event\ActionVariableEvent;
use Mush\Game\Entity\Collection\EventChain;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Game\Event\VariableEventInterface;
use Mush\Modifier\Entity\Config\EventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Enum\ModifierPriorityEnum;
use Mush\Status\Entity\Attempt;
use Mush\Status\Enum\StatusEnum;

class EventModifierService implements EventModifierServiceInterface
{
    private const ATTEMPT_INCREASE = 1.25;

    private ModifierHandlerServiceInterface $modifierHandlerService;
    private ModifierRequirementServiceInterface $modifierRequirementService;

    public function __construct(
        ModifierHandlerServiceInterface $modifierHandlerService,
        ModifierRequirementServiceInterface $modifierRequirementService,
    ) {
        $this->modifierHandlerService = $modifierHandlerService;
        $this->modifierRequirementService = $modifierRequirementService;
    }

    // return an array with all the event to dispatch
    // the event are returned in their priority order
    public function applyModifiers(AbstractGameEvent $initialEvent, array $priorities): EventChain
    {
        $modifiers = $initialEvent->getModifiersByPriorities($priorities);

        $events = new EventChain([$initialEvent]);

        // @TODO add a new modifier strategy to handle the increase due to attempts (require a better handling of the modifier "origin")
        // if the event is an action, we need to apply the increase due to successive attempts
        if ($initialEvent instanceof VariableEventInterface && \in_array(ModifierPriorityEnum::ATTEMPT_INCREASE, $priorities, true)) {
            $initialValue = $this->getInitValue($initialEvent);
            $initialEvent->setQuantity($initialValue);
        }

        // sort the modifiers to apply them in the correct order
        $modifiers = $modifiers->sortModifiers();

        foreach ($modifiers as $modifier) {
            $events = $this->applyModifier($modifier, $initialEvent, $events);
        }

        $initialEvent = $events->getInitialEvent();

        if ($initialEvent !== null) {
            $events->updateInitialEvent($initialEvent);
        }

        return $events;
    }

    private function applyModifier(
        GameModifier $modifier,
        AbstractGameEvent $initialEvent,
        EventChain $events,
    ): EventChain {
        $modifierConfig = $modifier->getModifierConfig();

        // Check if the modifier applies
        if (
            $modifierConfig instanceof EventModifierConfig
            && $modifierConfig->doModifierApplies($initialEvent)
            && $this->modifierRequirementService->checkModifier($modifierConfig, $modifier->getModifierHolder())
        ) {
            $handler = $this->modifierHandlerService->getModifierHandler($modifier);
            if ($handler === null) {
                throw new \LogicException("This modifierStrategy ({$modifierConfig->getModifierStrategy()}) is not handled");
            }
            $events = $handler->handleEventModifier($modifier, $events, $initialEvent->getEventName(), $initialEvent->getTags(), $initialEvent->getTime());

            // Let's add the tag of this modifier to the initial event
            $initialEvent = $events->getInitialEvent();
            // if event chain has been cut by a preventModifier return the EventChain
            if ($initialEvent === null) {
                return $events;
            }
            $initialEvent->addTag($modifierConfig->getModifierName() ?: $modifierConfig->getName());

            $events->updateInitialEvent($initialEvent);
        }

        return $events;
    }

    private function getInitValue(VariableEventInterface $event): float
    {
        $variable = $event->getVariable();
        $variableName = $variable->getName();
        $initialValue = $event->getQuantity();

        if ($event instanceof ActionVariableEvent
            && $variableName === ActionVariableEnum::PERCENTAGE_SUCCESS
        ) {
            /** @var ?Attempt $attemptStatus */
            $attemptStatus = $event->getAuthor()->getStatusByName(StatusEnum::ATTEMPT);

            if ($attemptStatus === null || $attemptStatus->getAction() !== $event->getActionConfig()->getActionName()->value) {
                $attemptNumber = 0;
            } else {
                $attemptNumber = $attemptStatus->getCharge();
            }

            return $initialValue * self::ATTEMPT_INCREASE ** $attemptNumber;
        }

        return $initialValue;
    }
}
