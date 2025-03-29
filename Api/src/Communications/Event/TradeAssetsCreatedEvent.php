<?php

namespace Mush\Communications\Event;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Event\AbstractGameEvent;

final class TradeAssetsCreatedEvent extends AbstractGameEvent
{
    public function __construct(
        private Daedalus $daedalus,
        protected array $tags = [],
        protected \DateTime $time = new \DateTime(),
    ) {
        parent::__construct($tags, $time);
    }

    public function getDaedalus(): Daedalus
    {
        return $this->daedalus;
    }
}
