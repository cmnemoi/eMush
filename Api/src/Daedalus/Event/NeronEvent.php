<?php

namespace Mush\Daedalus\Event;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\Neron;

final class NeronEvent extends DaedalusEvent
{
    public const string CPU_PRIORITY_CHANGED = 'cpu.priority.changed';
    public const string INHIBITION_TOGGLED = 'inhibition.toggled';

    private Neron $neron;

    public function __construct(Neron $neron, array $tags = [], \DateTime $time = new \DateTime())
    {
        $daedalus = $neron->getDaedalusInfo()->getDaedalus();
        if ($daedalus === null) {
            throw new \RuntimeException('Daedalus not found for NeronEvent!');
        }

        parent::__construct($daedalus, $tags, $time);

        $this->neron = $neron;
    }

    public function getNeron(): Neron
    {
        return $this->neron;
    }

    public function getDaedalus(): Daedalus
    {
        $daedalus = $this->neron->getDaedalusInfo()->getDaedalus();

        return $daedalus ?? throw new \RuntimeException('NeronEvent should have a Daedalus!');
    }
}
