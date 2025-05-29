<?php

declare(strict_types=1);

namespace Mush\Triumph\Event;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Triumph\Entity\TriumphConfig;

interface TriumphSourceEventInterface
{
    public function getDaedalus(): Daedalus;

    public function getEventName(): string;

    public function getTargetsForTriumph(TriumphConfig $triumphConfig): PlayerCollection;

    public function hasExpectedTagsFor(TriumphConfig $triumphConfig): bool;
}
