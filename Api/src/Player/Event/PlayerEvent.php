<?php

namespace Mush\Player\Event;

use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\RoomLog\Event\LoggableEventInterface;

class PlayerEvent extends PlayerCycleEvent implements LoggableEventInterface
{
    public const string NEW_PLAYER = 'new.player';
    public const string DEATH_PLAYER = 'death.player';
    public const string METAL_PLATE = 'metal.plate';
    public const string PANIC_CRISIS = 'panic.crisis';
    public const string CYCLE_DISEASE = 'cycle_disease';
    public const string INFECTION_PLAYER = 'infection.player';
    public const string CONVERSION_PLAYER = 'conversion.player';
    public const string END_PLAYER = 'end.player';
    public const string DELETE_PLAYER = 'delete.player';
    public const string CHANGED_PLACE = 'changed.place';
    public const string TITLE_ATTRIBUTED = 'title.attributed';
    public const string TITLE_REMOVED = 'title.removed';

    protected string $visibility = VisibilityEnum::PRIVATE;
    protected ?CharacterConfig $characterConfig = null;

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function setVisibility(string $visibility): self
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function getPlace(): Place
    {
        return $this->getPlayer()->getPlace();
    }

    public function getLogParameters(): array
    {
        $params = [
            'target_' . $this->getPlayer()->getLogKey() => $this->getPlayer()->getLogName(),
        ];

        $author = $this->getAuthor();
        if ($author !== null) {
            $params[$author->getLogKey()] = $author->getLogName();
        }

        return $params;
    }

    public function setCharacterConfig(CharacterConfig $characterConfig): self
    {
        $this->characterConfig = $characterConfig;

        return $this;
    }

    public function getCharacterConfig(): ?CharacterConfig
    {
        return $this->characterConfig;
    }
}
