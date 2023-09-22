<?php

namespace Mush\Modifier\Service;

use Mush\Action\Enum\ActionVariableEnum;
use Mush\Action\Event\ActionVariableEvent;
use Mush\Game\Entity\Collection\EventChain;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Game\Event\VariableEventInterface;
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
    public function applyModifiers(AbstractGameEvent $initialEvent): EventChain
    {
        $modifiers = $initialEvent->getModifiers();

        $events = new EventChain([$initialEvent]);

        // @TODO add a new modifier strategy to handle the increase due to attempts (require a better handling of the modifier "origin")
        // if the event is an action, we need to apply the increase due to successive attempts
        if ($initialEvent instanceof VariableEventInterface) {
            $initialValue = $this->getInitValue($initialEvent);
            $initialEvent->setQuantity($initialValue);
        }

        // sort the modifiers to apply them in the correct order
        $modifiers = $modifiers->sortModifiers();

        foreach ($modifiers as $modifier) {
            // Check if the modifier applies
            if (
                $modifier->getModifierConfig()->doModifierApplies($initialEvent)
                && $this->modifierRequirementService->checkModifier($modifier)
            ) {
                $handler = $this->modifierHandlerService->getModifierHandler($modifier);
                if ($handler === null) {
                    throw new \LogicException("This modifierStrategy ({$modifier->getModifierConfig()->getModifierStrategy()}) is not handled");
                }
                $events = $handler->handleEventModifier($modifier, $events, $initialEvent->getEventName(), $initialEvent->getTags(), $initialEvent->getTime());

                // Let's add the tag of this modifier to the initial event
                $initialEvent = $events->getInitialEvent();
                // if event chain has been cut by a preventModifier return the EventChain
                if ($initialEvent === null) {
                    return $events;
                }
                $initialEvent->addTag($modifier->getModifierConfig()->getModifierName() ?: $modifier->getModifierConfig()->getName());

                $events->updateInitialEvent($initialEvent);
            }
        }

        $initialEvent = $events->getInitialEvent();
        if ($initialEvent !== null) {
            $initialEvent->setIsModified(true);
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

            if ($attemptStatus === null || $attemptStatus->getAction() !== $event->getAction()->getActionName()) {
                $attemptNumber = 0;
            } else {
                $attemptNumber = $attemptStatus->getCharge();
            }

            return $initialValue * self::ATTEMPT_INCREASE ** $attemptNumber;
        }

        return $initialValue;
    }
}
