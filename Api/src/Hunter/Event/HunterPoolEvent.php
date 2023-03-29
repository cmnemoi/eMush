<?php

namespace Mush\Hunter\Event;

use Mush\Daedalus\Entity\Daedalus;

class HunterPoolEvent extends AbstractHunterEvent
{
    public const UNPOOL_HUNTERS = 'unpool.hunters';
    public const POOL_HUNTERS = 'pool.hunters';

    protected int $nbHunters;

    public function __construct(Daedalus $daedalus, int $nbHunters, array $tags, \DateTime $time)
    {
        parent::__construct($daedalus, $tags, $time);

        $this->nbHunters = $nbHunters;
    }

    public function getNbHunters(): int
    {
        return $this->nbHunters;
    }
}
