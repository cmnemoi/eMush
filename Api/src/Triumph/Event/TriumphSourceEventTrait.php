<?php

declare(strict_types=1);

namespace Mush\Triumph\Event;

use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Triumph\Entity\TriumphConfig;
use Mush\Triumph\Enum\TriumphScope;

trait TriumphSourceEventTrait
{
    public function getScopeTargetsForTriumph(TriumphConfig $triumphConfig): PlayerCollection
    {
        return match ($triumphConfig->getScope()) {
            TriumphScope::ALL_ACTIVE_HUMANS => $this->getDaedalus()->getAlivePlayers()->getHumanPlayer()->getActivePlayers(),
            TriumphScope::ALL_ALIVE_HUMANS => $this->getDaedalus()->getAlivePlayers()->getHumanPlayer(),
            TriumphScope::ALL_ALIVE_MUSHS => $this->getDaedalus()->getAlivePlayers()->getMushPlayer(),
            TriumphScope::ALL_ALIVE_PLAYERS => $this->getDaedalus()->getAlivePlayers(),
            TriumphScope::ALL_ACTIVE_HUMAN_EXPLORERS => $this->getDaedalus()->getExplorationOrThrow()->getActiveExplorators()->getHumanPlayer(),
            TriumphScope::ALL_ACTIVE_EXPLORERS => $this->getDaedalus()->getExplorationOrThrow()->getActiveExplorators(),
            TriumphScope::ALL_MUSHS => $this->getDaedalus()->getMushPlayers(),
            default => throw new \LogicException('Unsupported triumph scope: ' . $triumphConfig->getScope()->value),
        };
    }

    public function hasExpectedTagsFor(TriumphConfig $triumphConfig): bool
    {
        if (!$triumphConfig->hasTagConstraints()) {
            return true;
        }

        $anyConstraint = null;
        foreach ($triumphConfig->getTagConstraints() as $tag => $constraint) {
            switch ($constraint) {
                case TriumphSourceEventInterface::ANY_TAG:
                    $anyConstraint = $anyConstraint || $this->hasTag($tag);

                    break;

                case TriumphSourceEventInterface::ALL_TAGS:
                    if ($this->doesNotHaveTag($tag)) {
                        return false;
                    }

                    break;

                default:
                    throw new \LogicException('unexpected constraint type');
            }
        }

        if ($anyConstraint === null) {
            return true;
        }

        return $anyConstraint;
    }
}
