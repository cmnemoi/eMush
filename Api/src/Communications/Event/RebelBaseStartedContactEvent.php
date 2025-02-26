<?php

declare(strict_types=1);

namespace Mush\Communications\Event;

use Mush\Game\Event\AbstractGameEvent;

final class RebelBaseStartedContactEvent extends AbstractGameEvent
{
    public function __construct(
        public readonly int $daedalusId,
        array $tags = [],
        \DateTime $time = new \DateTime(),
    ) {
        parent::__construct($tags, $time);
    }
}
