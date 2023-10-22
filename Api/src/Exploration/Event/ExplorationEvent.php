<?php

declare(strict_types=1);

namespace Mush\Exploration\Event;

use Mush\Exploration\Entity\Exploration;
use Mush\Game\Event\AbstractGameEvent;

class ExplorationEvent extends AbstractGameEvent
{
    public const EXPLORATION_STARTED = 'exploration.started';
    public const EXPLORATION_FINISHED = 'exploration.finished';

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
}
