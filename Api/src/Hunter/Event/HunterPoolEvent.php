<?php

namespace Mush\Hunter\Event;

use Mush\Daedalus\Entity\Daedalus;

class HunterPoolEvent extends AbstractHunterEvent
{
    public const UNPOOL_HUNTERS = 'unpool.hunters';

    public function __construct(Daedalus $daedalus, array $tags, \DateTime $time)
    {
        parent::__construct($daedalus, $tags, $time);
    }
}
