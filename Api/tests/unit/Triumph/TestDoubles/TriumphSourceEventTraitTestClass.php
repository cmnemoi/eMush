<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Triumph\Event;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Triumph\Entity\TriumphConfig;
use Mush\Triumph\Event\TriumphSourceEventInterface;
use Mush\Triumph\Event\TriumphSourceEventTrait;

/**
 * @internal
 */
final class TriumphSourceEventTraitTestClass implements TriumphSourceEventInterface
{
    use TriumphSourceEventTrait;

    private array $tags;

    public function __construct(array $tags)
    {
        $this->tags = $tags;
    }

    public function getDaedalus(): Daedalus
    {
        // Return a stub or mock if needed, not used in these tests
        return new Daedalus();
    }

    public function getEventName(): string
    {
        return 'test';
    }

    public function getTargetsForTriumph(TriumphConfig $triumphConfig): PlayerCollection
    {
        // Return a stub, not used in these tests
        return new PlayerCollection();
    }

    public function hasTag(string $tag): bool
    {
        return \in_array($tag, $this->tags, true);
    }

    public function doesNotHaveTag(string $tag): bool
    {
        return !$this->hasTag($tag);
    }
}
