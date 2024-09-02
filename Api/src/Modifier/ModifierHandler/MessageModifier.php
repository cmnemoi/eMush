<?php

namespace Mush\Modifier\ModifierHandler;

use Mush\Communication\Event\MessageEvent;
use Mush\Communication\Services\MessageModifierServiceInterface;
use Mush\Game\Entity\Collection\EventChain;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Enum\ModifierStrategyEnum;

class MessageModifier extends AbstractModifierHandler
{
    protected string $name = ModifierStrategyEnum::MESSAGE_MODIFIER;

    private MessageModifierServiceInterface $messageModifierService;

    public function __construct(
        MessageModifierServiceInterface $messageModifierService,
    ) {
        $this->messageModifierService = $messageModifierService;
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
        if (!$initialEvent instanceof MessageEvent) {
            return $events;
        }

        // if the event already have been modified no need for extra changes
        if ($initialEvent->isModified()) {
            return $this->addModifierEvent($events, $modifier, $tags, $time);
        }

        $message = $this->messageModifierService->applyModifierEffects(
            $initialEvent->getMessage(),
            $initialEvent->getAuthor(),
            $modifierName
        );

        $initialEvent->setMessage($message);
        $events = $events->updateInitialEvent($initialEvent);

        return $this->addModifierEvent($events, $modifier, $tags, $time);
    }
}
