<?php

namespace Mush\Modifier\Service;

use Mush\Action\Enum\ActionVariableEnum;
use Mush\Action\Event\ActionVariableEvent;
use Mush\Disease\Enum\SymptomEnum;
use Mush\Game\Entity\GameVariable;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\Config\TriggerEventModifierConfig;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Modifier\Enum\VariableModifierModeEnum;
use Mush\Modifier\Event\ModifierEvent;
use Mush\Status\Entity\Attempt;
use Mush\Status\Enum\StatusEnum;

class EventModifierService implements EventModifierServiceInterface
{
    private const ATTEMPT_INCREASE = 1.25;

    private EventCreationServiceInterface $eventCreationService;

    public function __construct(
        EventCreationServiceInterface $eventCreationService,
    ) {
        $this->eventCreationService =$eventCreationService;
    }

    private function getModifiedValue(ModifierCollection $modifierCollection, ?float $initValue): int
    {
        $multiplicativeDelta = 1;
        $additiveDelta = 0;

        /** @var GameModifier $modifier */
        foreach ($modifierCollection as $modifier) {
            $modifierConfig = $modifier->getModifierConfig();
            if ($modifierConfig instanceof VariableEventModifierConfig) {
                switch ($modifierConfig->getMode()) {
                    case VariableModifierModeEnum::SET_VALUE:
                        return intval($modifierConfig->getDelta());
                    case VariableModifierModeEnum::ADDITIVE:
                        $additiveDelta += $modifierConfig->getDelta();
                        break;
                    case VariableModifierModeEnum::MULTIPLICATIVE:
                        $multiplicativeDelta *= $modifierConfig->getDelta();
                        break;
                    default:
                        throw new \LogicException('this modifier mode is not handled');
                }
            }
        }

        return $this->computeModifiedValue($initValue, $multiplicativeDelta, $additiveDelta);
    }

    private function getInitValue(VariableEventInterface $event): int
    {
        $variable = $event->getVariable();
        $variableName = $variable->getName();
        $initialValue = $event->getQuantity();

        if ($event instanceof ActionVariableEvent &&
            $variableName === ActionVariableEnum::PERCENTAGE_SUCCESS
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

    private function computeModifiedValue(?float $initValue, float $multiplicativeDelta, float $additiveDelta): int
    {
        if ($initValue === null) {
            return 0;
        }

        $modifiedValue = intval($initValue * $multiplicativeDelta + $additiveDelta);
        if ($initValue * $modifiedValue < 0) {
            return 0;
        }

        return $modifiedValue;
    }

    public function applyVariableModifiers(ModifierCollection $modifiers, AbstractGameEvent $event): AbstractGameEvent
    {
        if (!($event instanceof VariableEventInterface)) {
            throw new \Exception('variableEventModifiers only apply on variableEventInterface');
        }

        $initialValue = $this->getInitValue($event);

        $newValue = $this->getModifiedValue($modifiers, $initialValue);

        $event->setQuantity($newValue);

        return $event;
    }






    // return an array with all the event to dispatch
    // the event are returned in their priority order
    public function applyModifiers(ModifierCollection $modifiers, AbstractGameEvent $event): array
    {
        // first we need to apply all variable modifiers
        // (because we need all modifiers at one to apply the formula)
        $event = $this->applyVariableModifiers($modifiers->getVariableEventModifiers(), $event);

        $events[0] = [$event];

        foreach ($modifiers as $modifier) {
            $modifierConfig = $modifier->getModifierConfig();
            $modifierName = $modifierConfig->getModifierName();

            if ($modifierConfig instanceof TriggerEventModifierConfig) {
                $events = $this->handleTriggerEventModifier(
                    $modifierConfig,
                    $events,
                    $modifier->getModifierHolder(),
                    $event
                );
            } elseif (in_array($modifierName, self::MESSAGE_MODIFIERS)) {
                $event = $this->handleMessageModifier($modifierName, $event);
            }
        }

        return $events;
    }

    private function handleTriggerEventModifier(
        TriggerEventModifierConfig $modifierConfig,
        array $events,
        ModifierHolder $modifierHolder,
        AbstractGameEvent $event
    ): array
    {
        $eventConfig = $modifierConfig->getTriggeredEvent();

        $newEvents = $this->eventCreationService->createEvents(
            $eventConfig,
            $modifierHolder,
            $event->getTags(),
            $event->getTime()
        );

        if ($modifierConfig->getReplaceEvent() && in_array(null, $newEvents)) {
            return [];
        } elseif ($modifierConfig->getReplaceEvent()) {
            $events[0] = $newEvents;
        } else {
            $events[$modifierConfig->getPriority()][] = $newEvents;
        }

        return $events;
    }

    private function createAppliedModifiersEvent(GameModifier $modifier, array $tags, \DateTime $time): ModifierEvent
    {
        $modifierEvent = new ModifierEvent($modifier, $tags, $time, true);
        $modifierEvent->setEventName(ModifierEvent::APPLY_MODIFIER);

        return $modifierEvent;
    }
}
