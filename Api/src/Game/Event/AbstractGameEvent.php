<?php

namespace Mush\Game\Event;

use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Player\Entity\Player;
use Symfony\Contracts\EventDispatcher\Event;

class AbstractGameEvent extends Event
{
    private string $eventName;
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
