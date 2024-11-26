<?php

declare(strict_types=1);

namespace Mush\Modifier\ModifierHandler;

use Mush\Communication\Services\MessageService;
use Mush\Game\Entity\Collection\EventChain;
use Mush\Modifier\Entity\Config\EventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Modifier\Enum\ModifierStrategyEnum;

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

        // Mute players can talk in Mush channel
        if ($this->eventAboutMutePlayerPostingInMushChannel($modifierHolder, $tags)) {
            return $this->addModifierEvent($events, $modifier, $tags, $time);
        }

        $events = $events->stopEvents($modifierConfig->getPriorityAsInteger());

        return $this->addModifierEvent($events, $modifier, $tags, $time);
    }

    private function eventAboutMutePlayerPostingInMushChannel(ModifierHolderInterface $modifierHolder, array $tags): bool
    {
        return \in_array(MessageService::MUTE_PLAYER_SPEAKING_IN_MUSH_CHANNEL, $tags, true);
    }
}
