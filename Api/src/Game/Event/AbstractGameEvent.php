<?php

namespace Mush\Game\Event;

use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Player\Entity\Player;
use Symfony\Contracts\EventDispatcher\Event;

class AbstractGameEvent extends Event
{
    private string $eventName;
    private bool $isModified = false;
    protected ?Player $player = null;
    protected \DateTime $time;
    protected array $tags;

    public function __construct(array $tags, \DateTime $time)
    {
        $this->tags = $tags;
        $this->time = $time;
    }

    public function setEventName(string $eventName): void
    {
        $this->eventName = $eventName;
    }

    public function getEventName(): string
    {
        return $this->eventName;
    }

    public function isModified(): bool
    {
        return $this->isModified;
    }

    public function setIsModified(bool $isModified): self
    {
        $this->isModified = $isModified;

        return $this;
    }

    public function setPlayer(?Player $player): self
    {
        $this->player = $player;

        return $this;
    }

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function getTime(): \DateTime
    {
        return $this->time;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function setTags(array $tags): self
    {
        $this->tags = $tags;

        return $this;
    }

    public function addTag(string $tag): self
    {
        $this->tags[] = $tag;

        return $this;
    }

    public function haveTag(string $tag): bool
    {
        return in_array($tag, $this->tags);
    }

    // TODO: might want to create a `tagConstaints` property instead
    /**
     * Returns true if the event has all the tags in the array.
     * If `$all` is false, returns true if the event has at least one of the tags.
     */
    public function haveTags(array $tags, bool $all = true): bool
    {
        if ($all) {
            return count(array_intersect($tags, $this->tags)) === count($tags);
        }

        return count(array_intersect($tags, $this->tags)) > 0;
    }

    public function mapLog(array $map): ?string
    {
        $logs = array_intersect_key($map, array_flip($this->tags));

        if (count($logs) > 0) {
            return reset($logs);
        }

        return null;
    }

    public function getModifiers(): ModifierCollection
    {
        $player = $this->player;

        if ($player === null) {
            return new ModifierCollection();
        }

        return $player->getAllModifiers()->getEventModifiers($this);
    }
}
