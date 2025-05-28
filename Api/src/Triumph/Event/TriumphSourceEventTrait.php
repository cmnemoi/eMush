<?php

declare(strict_types=1);

namespace Mush\Triumph\Event;

use Mush\Triumph\Entity\TriumphConfig;

trait TriumphSourceEventTrait
{
    public function hasExpectedTagsFor(TriumphConfig $triumphConfig): bool
    {
        return $this->hasAllTags($triumphConfig->getTargetedEventExpectedTags());
    }
}
