<?php

namespace Mush\Daedalus\Event;

use Mush\Daedalus\Entity\Neron;

final class NeronEvent extends DaedalusEvent
{
    public const CPU_PRIORITY_CHANGED = 'cpu.priority.changed';

    private Neron $neron;

    public function __construct(Neron $neron, array $tags, \DateTime $time)
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
}
