<?php

declare(strict_types=1);

namespace Mush\Exploration\Event;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusStatistics;
use Mush\Exploration\Entity\Exploration;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;
use Mush\Triumph\Enum\TriumphTarget;
use Mush\Triumph\Event\TriumphSourceEventInterface;
use Mush\Triumph\Event\TriumphSourceEventTrait;

class ExplorationEvent extends AbstractGameEvent implements TriumphSourceEventInterface
{
    use TriumphSourceEventTrait;

    public const string ALL_EXPLORATORS_ARE_DEAD = 'exploration.all_explorators_are_dead';
    public const string ALL_EXPLORATORS_STUCKED = 'exploration.all_explorators_stucked';
    public const string ALL_SECTORS_VISITED = 'exploration.all_sectors_visited';
    public const string EXPLORATION_NEW_CYCLE = 'exploration.new_cycle';
    public const string EXPLORATION_STARTED = 'exploration.started';
    public const string EXPLORATION_FINISHED = 'exploration.finished';

    protected Exploration $exploration;

    public function __construct(
        Exploration $exploration,
        array $tags,
        \DateTime $time,
    ) {
        parent::__construct($tags, $time);
        $this->exploration = $exploration;
    }

    public function getExploration(): Exploration
    {
        return $this->exploration;
    }

    public function getDaedalus(): Daedalus
    {
        return $this->exploration->getDaedalus();
    }

    public function getStartPlace(): Place
    {
        return $this->getDaedalus()->getPlaceByNameOrThrow($this->exploration->getStartPlaceName());
    }

    public function getDaedalusStatistics(): DaedalusStatistics
    {
        return $this->getDaedalus()->getDaedalusInfo()->getDaedalusStatistics();
    }

    protected function getEventSpecificTargets(TriumphTarget $targetSetting, PlayerCollection $scopeTargets): PlayerCollection
    {
        return match ($targetSetting) {
            TriumphTarget::ACTIVE_EXPLORERS => $scopeTargets->filter(fn (Player $player) => $this->exploration->getNotLostActiveExplorators()->contains($player)),
            default => throw new \LogicException("Triumph target {$targetSetting->toString()} is not supported"),
        };
    }
}
