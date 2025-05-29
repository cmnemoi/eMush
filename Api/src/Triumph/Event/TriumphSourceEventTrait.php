<?php

declare(strict_types=1);

namespace Mush\Triumph\Event;

use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Triumph\Entity\TriumphConfig;
use Mush\Triumph\Enum\TriumphScope;

trait TriumphSourceEventTrait
{
    public function getTargetsForTriumph(TriumphConfig $triumphConfig): PlayerCollection
    {
        $targets = match ($triumphConfig->getScope()) {
            TriumphScope::ALL_ACTIVE_HUMANS => $this->getDaedalus()->getAlivePlayers()->getHumanPlayer()->getActivePlayers(),
            TriumphScope::ALL_ALIVE_HUMANS => $this->getDaedalus()->getAlivePlayers()->getHumanPlayer(),
            TriumphScope::ALL_ALIVE_MUSHS => $this->getDaedalus()->getAlivePlayers()->getMushPlayer(),
            TriumphScope::ALL_ACTIVE_HUMAN_EXPLORERS => $this->getDaedalus()->getExplorationOrThrow()->getActiveExplorators()->getHumanPlayer(),
            TriumphScope::ALL_ACTIVE_EXPLORERS => $this->getDaedalus()->getExplorationOrThrow()->getActiveExplorators(),
            TriumphScope::ALL_MUSHS => $this->getDaedalus()->getMushPlayers(),
            default => throw new \LogicException('Unsupported triumph scope: ' . $triumphConfig->getScope()->value),
        };

        if ($triumphConfig->hasATarget()) {
            $targets = $targets->getAllByName($triumphConfig->getTarget());
        }

        return $targets;
    }

    public function hasExpectedTagsFor(TriumphConfig $triumphConfig): bool
    {
        return $this->hasAllTags($triumphConfig->getTargetedEventExpectedTags());
    }
}
