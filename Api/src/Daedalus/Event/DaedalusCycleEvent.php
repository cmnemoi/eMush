<?php

namespace Mush\Daedalus\Event;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Modifier\Entity\Collection\ModifierCollection;

class DaedalusCycleEvent extends AbstractGameEvent
{
    public const DAEDALUS_NEW_CYCLE = 'daedalus.new.cycle';

    protected Daedalus $daedalus;

    public function __construct(Daedalus $daedalus, array $tags, \DateTime $time)
    {
        parent::__construct($tags, $time);

        $this->daedalus = $daedalus;
    }

    public function getDaedalus(): Daedalus
    {
        return $this->daedalus;
    }

    public function getModifiers(): ModifierCollection
    {
        $player = $this->author;

        if ($player === null) {
            return $this->getDaedalus()->getAllModifiers()->getEventModifiers($this);
        }

        return $player->getAllModifiers()->getEventModifiers($this);
    }
}
