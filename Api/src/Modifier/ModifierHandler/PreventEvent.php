<?php

declare(strict_types=1);

namespace Mush\Modifier\ModifierHandler;

use Mush\Communication\Services\MessageService;
use Mush\Game\Entity\Collection\EventChain;
use Mush\Modifier\Entity\Config\EventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Modifier\Enum\ModifierStrategyEnum;
use Mush\Player\Entity\Player;

final class PreventEvent extends AbstractModifierHandler
{
    protected string $name = ModifierStrategyEnum::PREVENT_EVENT;

    public function handleEventModifier(
        GameModifier $modifier,
        EventChain $events,
        string $eventName,
        array $tags,
        \DateTime $time
    ): EventChain {
        /** @var EventModifierConfig $modifierConfig */
        $modifierConfig = $modifier->getModifierConfig();
        $modifierHolder = $modifier->getModifierHolder();

        // Mush players can post in Mush channel whatever the conditions
        if ($this->eventAboutMushPlayerPostingInMushChannel($modifierHolder, $tags)) {
            return $this->addModifierEvent($events, $modifier, $tags, $time);
        }

        $events = $events->stopEvents($modifierConfig->getPriorityAsInteger());

        return $this->addModifierEvent($events, $modifier, $tags, $time);
    }

    private function eventAboutMushPlayerPostingInMushChannel(ModifierHolderInterface $modifierHolder, array $tags): bool
    {
        return \in_array(MessageService::MUSH_SPEAKING_IN_MUSH_CHANNEL, $tags, true)
            && $modifierHolder instanceof Player
            && $modifierHolder->isMush();
    }
}
