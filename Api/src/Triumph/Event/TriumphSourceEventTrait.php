<?php

declare(strict_types=1);

namespace Mush\Triumph\Event;

use Mush\Game\Enum\CharacterEnum;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;
use Mush\Skill\Enum\SkillEnum;
use Mush\Triumph\Entity\TriumphConfig;
use Mush\Triumph\Enum\TriumphScope;
use Mush\Triumph\Enum\TriumphTarget;

trait TriumphSourceEventTrait
{
    public function getTriumphTargets(TriumphConfig $triumphConfig): PlayerCollection
    {
        $this->addEventTags();
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

    /**
     * This function retrieves tags from events for triumph config tag constraints.
     * Logic should be implemented in the class.
     */
    protected function addEventTags(): void {}

    protected function getEventSpecificTargets(TriumphTarget $targetSetting, PlayerCollection $scopeTargets): PlayerCollection
    {
        throw new \LogicException('You need to implement getEventSpecificTargets in ' . static::class . ' if using a non-character target!');
    }

    private function getScopeTargetsForTriumph(TriumphConfig $triumphConfig): PlayerCollection
    {
        $triumphScope = $triumphConfig->getScope();

        $possibleCharacterName = substr($triumphScope->value, 9);
        if (CharacterEnum::exists($possibleCharacterName)) {
            return $this->getDaedalus()->getAlivePlayers()->getHumanPlayer()->getAllByName($possibleCharacterName);
        }

        return match ($triumphConfig->getScope()) {
            TriumphScope::ALL_ALIVE_HUMANS => $this->getDaedalus()->getAlivePlayers()->getHumanPlayer(),
            TriumphScope::ALL_ALIVE_MUSHS => $this->getDaedalus()->getAlivePlayers()->getMushPlayer(),
            TriumphScope::ALL_ALIVE_PLAYERS => $this->getDaedalus()->getAlivePlayers(),
            TriumphScope::ALL_ALIVE_HUMAN_PHARMACISTS => $this->getDaedalus()->getAlivePlayers()->getHumanPlayer()->filter(static fn (Player $player) => $player->hasAnySkill([SkillEnum::NURSE, SkillEnum::BIOLOGIST, SkillEnum::MEDIC])),
            TriumphScope::ALL_ALIVE_HUMAN_TECHNICIANS => $this->getDaedalus()->getAlivePlayers()->getHumanPlayer()->filter(static fn (Player $player) => $player->hasSkill(SkillEnum::TECHNICIAN)),
            TriumphScope::ALL_MUSHS => $this->getDaedalus()->getMushPlayers(),
            TriumphScope::ALL_PLAYERS => $this->getDaedalus()->getPlayers(),
            default => throw new \LogicException('Unsupported triumph scope: ' . $triumphConfig->getScope()->value),
        };
    }
}
