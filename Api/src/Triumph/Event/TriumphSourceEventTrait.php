<?php

declare(strict_types=1);

namespace Mush\Triumph\Event;

use Mush\Game\Enum\CharacterEnum;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Triumph\Entity\TriumphConfig;
use Mush\Triumph\Enum\TriumphScope;

trait TriumphSourceEventTrait
{
    public function getTriumphTargets(TriumphConfig $triumphConfig): PlayerCollection
    {
        if (!$this->hasExpectedTagsFor($triumphConfig)) {
            return new PlayerCollection();
        }

        $scopeTargets = $this->getScopeTargetsForTriumph($triumphConfig);
        if (!$triumphConfig->hasATarget()) {
            return $scopeTargets;
        }

        return $this->filterTargetsBySetting($triumphConfig->getTarget(), $scopeTargets);
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

    protected function getEventSpecificTargets(string $targetSetting, PlayerCollection $scopeTargets): PlayerCollection
    {
        throw new \LogicException('Not implemented');
    }

    private function filterTargetsBySetting(string $targetSetting, PlayerCollection $scopeTargets): PlayerCollection
    {
        if (CharacterEnum::exists($targetSetting)) {
            return $scopeTargets->getAllByName($targetSetting);
        }

        return $this->getEventSpecificTargets($targetSetting, $scopeTargets);
    }

    private function getScopeTargetsForTriumph(TriumphConfig $triumphConfig): PlayerCollection
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
}
