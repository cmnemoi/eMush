<?php

declare(strict_types=1);

namespace Mush\Project\Event;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Event\AbstractGameEvent;

final class BricBrocProjectWorkedEvent extends AbstractGameEvent
{
    public function __construct(
        private Daedalus $daedalus,
        array $tags = [],
        \DateTime $time = new \DateTime(),
    ) {
        parent::__construct($tags, $time);
    }

    public function getDaedalus(): Daedalus
    {
        return $this->daedalus;
    }
}
