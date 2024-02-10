<?php

namespace Mush\Modifier\ModifierHandler;

use Mush\Disease\Event\SymptomEvent;
use Mush\Game\Entity\Collection\EventChain;
use Mush\Modifier\Entity\Config\EventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Enum\ModifierStrategyEnum;
use Mush\Player\Entity\Player;

class SymptomModifierHandler extends AbstractModifierHandler
{
    protected string $name = ModifierStrategyEnum::SYMPTOM_MODIFIER;

    public function handleEventModifier(
        GameModifier $modifier,
        EventChain $events,
        string $eventName,
        array $tags,
        \DateTime $time
    ): EventChain {
        $modifierName = $modifier->getModifierConfig()->getModifierName();
        $initialEvent = $events->getInitialEvent();
        /** @var EventModifierConfig $modifierConfig */
        $modifierConfig = $modifier->getModifierConfig();

        // if the initial event do not exist anymore
        if ($initialEvent === null) {
            return $events;
        }
        if ($modifierName === null) {
            throw new \Exception('A modifier with a SymptomModifier strategy should have a ModifierName');
        }

        $player = $modifier->getModifierHolder();
        if (!($player instanceof Player)) {
            return $events;
        }

        $symptomEvent = new SymptomEvent(
            $player,
            $modifierName,
            $tags,
            $time
        );
        $symptomEvent
            ->setPriority($modifierConfig->getPriorityAsInteger())
            ->setEventName(SymptomEvent::TRIGGER_SYMPTOM)
        ;

        $events = $events->addEvent($symptomEvent);

        return $this->addModifierEvent($events, $modifier, $tags, $time);
    }
}
