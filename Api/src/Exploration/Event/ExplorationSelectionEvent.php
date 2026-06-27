<?php

declare(strict_types=1);

namespace Mush\Exploration\Event;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Exploration\Entity\Exploration;
use Mush\Game\Entity\Collection\ProbaCollection;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Player\Entity\Player;
use Mush\Triumph\Event\TriumphSourceEventTrait;

class ExplorationSelectionEvent extends AbstractGameEvent
{
    use TriumphSourceEventTrait;

    public const string SECTOR_SELECTION = 'exploration.sector_selection';

    protected Exploration $exploration;
    protected ProbaCollection $planetSectorEvents;

    public function __construct(
        Exploration $exploration,
        ProbaCollection $events,
        array $tags,
        \DateTime $time,
        ?Player $author = null
    ) {
        parent::__construct($tags, $time);
        $this->exploration = $exploration;
        $this->author = $author;
        $this->planetSectorEvents = $events;
    }

    public function getExploration(): Exploration
    {
        return $this->exploration;
    }

    public function getDaedalus(): Daedalus
    {
        return $this->exploration->getDaedalus();
    }

    public function setPlanetSectorEvents(ProbaCollection $planetSectorEvents): static
    {
        $this->planetSectorEvents = $planetSectorEvents;

        return $this;
    }

    public function getPlanetSectorEvents(): ProbaCollection
    {
        return $this->planetSectorEvents;
    }

    public function getModifiersByPriorities(array $priorities): ModifierCollection
    {
        $player = $this->author;

        if (!$player || $player->isNull()) {
            return new ModifierCollection();
        }

        return $player->getAllModifiers()->getExplorationEventModifiers($this, $priorities);
    }
}
