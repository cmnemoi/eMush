<?php

namespace Mush\Daedalus\Event;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;

class DaedalusInitEvent extends DaedalusCycleEvent
{
    public const NEW_DAEDALUS = 'new.daedalus';

    private DaedalusConfig $daedalusConfig;

    public function __construct(Daedalus $daedalus, DaedalusConfig $daedalusConfig, string $reason, \DateTime $time)
    {
        parent::__construct($daedalus, $reason, $time);

        $this->daedalusConfig = $daedalusConfig;
    }

    public function getDaedalusConfig(): DaedalusConfig
    {
        return $this->daedalusConfig;
    }
}
