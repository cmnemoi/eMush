<?php

declare(strict_types=1);

namespace Mush\Communications\Event;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Triumph\Event\TriumphSourceEventInterface;
use Mush\Triumph\Event\TriumphSourceEventTrait;

final class RebelBaseDecodedEvent extends AbstractGameEvent implements TriumphSourceEventInterface
{
    use TriumphSourceEventTrait;

    public readonly int $daedalusId;

    public function __construct(
        public readonly Daedalus $daedalus,
        array $tags = [],
        \DateTime $time = new \DateTime(),
    ) {
        $this->daedalusId = $daedalus->getId();
        parent::__construct($tags, $time);
    }

    public function getDaedalus(): Daedalus
    {
        return $this->daedalus;
    }
}
