<?php

declare(strict_types=1);

namespace Mush\Triumph\Event;

use Mush\Game\Enum\CharacterEnum;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Triumph\Entity\TriumphConfig;
use Mush\Triumph\Enum\TriumphScope;
use Mush\Triumph\Enum\TriumphTarget;

trait TriumphSourceEventTrait
{
    public function getTriumphTargets(TriumphConfig $triumphConfig): PlayerCollection
    {
        if (!$this->hasExpectedTagsFor($triumphConfig)) {
            return new PlayerCollection();
        }

        $scopeTargets = $this->getScopeTargetsForTriumph($triumphConfig);
        if (!$triumphConfig->hasATargetSetting()) {
            return $scopeTargets;
        }

        return $this->getEventSpecificTargets($triumphConfig->getTargetSetting(), $scopeTargets);
    }

    public function hasExpectedTagsFor(TriumphConfig $triumphConfig): bool
    {
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

                case TriumphSourceEventInterface::NONE_TAGS:
                    if ($this->hasTag($tag)) {
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

    protected function getEventSpecificTargets(TriumphTarget $targetSetting, PlayerCollection $scopeTargets): PlayerCollection
    {
        throw new \LogicException('Not implemented');
    }

    private function getScopeTargetsForTriumph(TriumphConfig $triumphConfig): PlayerCollection
    {
        $triumphScope = $triumphConfig->getScope();

        $possibleCharacterName = substr($triumphScope->value, 9);
        if (CharacterEnum::exists($possibleCharacterName)) {
            return $this->getDaedalus()->getAlivePlayers()->getHumanPlayer()->getAllByName($possibleCharacterName);
        }

        return match ($triumphConfig->getScope()) {
            TriumphScope::ALL_ACTIVE_HUMANS => $this->getDaedalus()->getAlivePlayers()->getHumanPlayer()->getActivePlayers(),
            TriumphScope::ALL_ALIVE_HUMANS => $this->getDaedalus()->getAlivePlayers()->getHumanPlayer(),
            TriumphScope::ALL_ALIVE_MUSHS => $this->getDaedalus()->getAlivePlayers()->getMushPlayer(),
            TriumphScope::ALL_ALIVE_PLAYERS => $this->getDaedalus()->getAlivePlayers(),
            TriumphScope::ALL_MUSHS => $this->getDaedalus()->getMushPlayers(),
            default => throw new \LogicException('Unsupported triumph scope: ' . $triumphConfig->getScope()->value),
        };
    }
}
