<?php

namespace Mush\Modifier\ModifierHandler;

use Mush\Disease\Service\SymptomHandlerServiceInterface;
use Mush\Game\Entity\Collection\EventChain;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Enum\ModifierStrategyEnum;
use Mush\Player\Entity\Player;

class SymptomModifierHandler extends AbstractModifierHandler
{
    protected string $name = ModifierStrategyEnum::SYMPTOM_MODIFIER;

    private SymptomHandlerServiceInterface $symptomHandlerService;

    public function __construct(
        SymptomHandlerServiceInterface $symptomHandlerService,
    ) {
        $this->symptomHandlerService = $symptomHandlerService;
    }

    public function handleEventModifier(
        GameModifier $modifier,
        EventChain $events,
        string $eventName,
        array $tags,
        \DateTime $time
    ): EventChain {
        $modifierName = $modifier->getModifierConfig()->getModifierName();
        $initialEvent = $events->getInitialEvent();

        // if the initial event do not exist anymore
        if ($initialEvent === null) {
            return $events;
        }
        if ($modifierName === null) {
            throw new \Exception('A modifier with a SymptomModifier strategy should have a ModifierName');
        }

        $symptomHandler = $this->symptomHandlerService->getSymptomHandler(
            $modifierName
        );

        // some symptoms are only a message, there is no handler for those
        if ($symptomHandler === null) {
            return $this->addModifierEvent($events, $modifier, $tags, $time);
        }

        $player = $modifier->getModifierHolder();
        if (!($player instanceof Player)) {
            return $events;
        }

        $symptomEvents = $symptomHandler->applyEffects(
            $player,
            $modifier->getModifierConfig()->getPriorityAsInteger(),
            $tags,
            $time
        );

        $events = $events->addEvents($symptomEvents);

        return $this->addModifierEvent($events, $modifier, $tags, $time);
    }
}
