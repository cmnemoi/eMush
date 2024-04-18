<?php

namespace Mush\Modifier\ModifierHandler;

use Mush\Game\Entity\Collection\EventChain;
use Mush\Game\Event\VariableEventInterface;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Enum\ModifierStrategyEnum;
use Mush\Modifier\Enum\VariableModifierModeEnum;

class VariableModifier extends AbstractModifierHandler
{
    protected string $name = ModifierStrategyEnum::VARIABLE_MODIFIER;

    public function handleEventModifier(
        GameModifier $modifier,
        EventChain $events,
        string $eventName,
        array $tags,
        \DateTime $time
    ): EventChain {
        /** @var VariableEventModifierConfig $modifierConfig */
        $modifierConfig = $modifier->getModifierConfig();
        $initialEvent = $events->getInitialEvent();

        // if the initial event do not exist anymore
        if (!$initialEvent instanceof VariableEventInterface) {
            return $events;
        }

        // if the event already have been modified no need for extra changes
        if ($initialEvent->isModified()) {
            return $this->addModifierEvent($events, $modifier, $tags, $time);
        }

        $mode = $modifierConfig->getMode();
        $modifierQuantity = $modifierConfig->getDelta();
        $eventQuantity = $initialEvent->getQuantity();

        switch ($mode) {
            case VariableModifierModeEnum::SET_VALUE:
                $initialEvent->setQuantity($modifierConfig->getDelta());

                break;

            case VariableModifierModeEnum::ADDITIVE:
                $initialEvent->setQuantity($modifierQuantity + $eventQuantity);

                break;

            case VariableModifierModeEnum::MULTIPLICATIVE:
                $initialEvent->setQuantity($modifierQuantity * $eventQuantity);

                break;

            default:
                throw new \LogicException("This variableModifierMode is not handled {$mode}");
        }

        $events->updateInitialEvent($initialEvent);

        return $this->addModifierEvent($events, $modifier, $tags, $time);
    }
}
