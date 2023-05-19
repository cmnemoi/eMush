<?php

namespace Mush\Disease\Event;

use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Event\LoggableEventInterface;

class SymptomEvent extends AbstractGameEvent implements LoggableEventInterface
{
    public const TRIGGER_SYMPTOM = 'trigger.symptom';
    private string $symptomName;
    private string $visibility = VisibilityEnum::PUBLIC;

    public function __construct(
        string $symptomName,
        Player $player,
        array $tags,
        \DateTime $time
    ) {
        $this->symptomName = $symptomName;
        $this->author = $player;

        parent::__construct($tags, $time);
    }

    public function setSymptomName(string $symptomName): self
    {
        $this->symptomName = $symptomName;

        return $this;
    }

    public function getSymptomName(): string
    {
        return $this->symptomName;
    }

    public function getAuthor(): Player
    {
        $player = $this->author;
        if ($player === null) {
            throw new \Exception('symptomEvent should have a player');
        }

        return $player;
    }

    public function getPlace(): Place
    {
        return $this->getAuthor()->getPlace();
    }

    public function setVisibility(string $visibility): self
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function getLogParameters(): array
    {
        return [
            $this->getAuthor()->getLogKey() => $this->getAuthor()->getLogName(),
        ];
    }

    public function getModifiers(): ModifierCollection
    {
        return $this->getAuthor()->getAllModifiers()->getEventModifiers($this)->getTargetModifiers(false);
    }
}
