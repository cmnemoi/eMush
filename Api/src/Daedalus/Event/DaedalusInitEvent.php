<?php

namespace Mush\Daedalus\Event;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;

class DaedalusInitEvent extends DaedalusCycleEvent
{
    public const string NEW_DAEDALUS = 'new.daedalus';

    private DaedalusConfig $daedalusConfig;

    public function __construct(Daedalus $daedalus, DaedalusConfig $daedalusConfig, array $tags, \DateTime $time)
    {
        parent::__construct($daedalus, $tags, $time);

        $this->daedalusConfig = $daedalusConfig;
    }

    public function getDaedalusConfig(): DaedalusConfig
    {
        return $this->daedalusConfig;
    }
}
