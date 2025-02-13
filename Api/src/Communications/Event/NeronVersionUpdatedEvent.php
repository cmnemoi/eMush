<?php

namespace Mush\Communications\Event;

use Mush\Game\Event\AbstractGameEvent;

final class NeronVersionUpdatedEvent extends AbstractGameEvent
{
    public function __construct(
        public readonly int $daedalusId,
        public readonly bool $majorVersionUpdated,
        array $tags = [],
        \DateTime $time = new \DateTime()
    ) {
        parent::__construct($tags, $time);
    }
}
