<?php

namespace Mush\Game\Event;

use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Player\Entity\Player;
use Symfony\Contracts\EventDispatcher\Event;

class AbstractGameEvent extends Event
{
    protected ?Player $author = null;
    protected \DateTime $time;
    protected array $tags;
    private string $eventName;
    private bool $isModified = false;
    private int $priority = 0;

    public function __construct(array $tags, \DateTime $time)
    {
        $this->tags = $tags;
        $this->time = $time;
    }

    public function setEventName(string $eventName): self
    {
        $this->eventName = $eventName;

        return $this;
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

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function setAuthor(?Player $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getAuthor(): ?Player
    {
        return $this->author;
    }

    public function getAuthorOrThrow(): Player
    {
        $author = $this->getAuthor();

        return $author !== null ? $author : throw new \LogicException('This event does not have an author');
    }

    public function hasAuthor(): bool
    {
        return $this->author !== null;
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

    public function getFirstTag(): string
    {
        return $this->tags[0];
    }

    public function addTag(string $tag): self
    {
        if (!$this->hasTag($tag)) {
            $this->tags[] = $tag;
        }

        return $this;
    }

    public function addTags(array $tags): self
    {
        foreach ($tags as $tag) {
            $this->addTag($tag);
        }

        return $this;
    }

    public function hasTag(string $tag): bool
    {
        return \in_array($tag, $this->tags, true);
    }

    public function hasAnyTag(array $tags): bool
    {
        return \count(array_intersect($tags, $this->tags)) > 0;
    }

    public function hasAllTags(array $tags): bool
    {
        return \count(array_intersect($tags, $this->tags)) === \count($tags);
    }

    public function doesNotHaveTag(string $tag): bool
    {
        return $this->hasTag($tag) === false;
    }

    public function mapLog(array $map): ?string
    {
        $logs = array_intersect_key($map, array_flip($this->tags));

        if (\count($logs) > 0) {
            return reset($logs);
        }

        return null;
    }

    public function getModifiersByPriorities(array $priorities): ModifierCollection
    {
        $player = $this->author;

        if (!$player || $player->isNull()) {
            return new ModifierCollection();
        }

        return $player->getAllModifiers()->getEventModifiers($this, $priorities);
    }
}
