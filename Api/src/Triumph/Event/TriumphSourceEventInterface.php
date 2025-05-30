<?php

declare(strict_types=1);

namespace Mush\Triumph\Event;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Triumph\Entity\TriumphConfig;

interface TriumphSourceEventInterface
{
    public const ANY_TAG = 'any_tag';
    public const ALL_TAGS = 'all_tags';

    public function doesNotHaveTag(string $tag): bool;

    public function getDaedalus(): Daedalus;

    public function getEventName(): string;

    public function getScopeTargetsForTriumph(TriumphConfig $triumphConfig): PlayerCollection;

    public function hasTag(string $tag): bool;

    public function hasExpectedTagsFor(TriumphConfig $triumphConfig): bool;
}
