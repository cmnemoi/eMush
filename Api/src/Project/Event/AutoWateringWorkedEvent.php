<?php

declare(strict_types=1);

namespace Mush\Project\Event;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Event\AbstractGameEvent;

final class AutoWateringWorkedEvent extends AbstractGameEvent
{
    public const string AUTO_WATERING_WORKED = 'project.auto_watering_worked';

    public function __construct(
        private readonly int $numberOfFiresPrevented,
        private readonly Daedalus $daedalus,
        array $tags = [],
        \DateTime $time = new \DateTime(),
    ) {
        parent::__construct($tags, $time);
    }

    public function getNumberOfFiresPrevented(): int
    {
        return $this->numberOfFiresPrevented;
    }

    public function getDaedalus(): Daedalus
    {
        return $this->daedalus;
    }
}
