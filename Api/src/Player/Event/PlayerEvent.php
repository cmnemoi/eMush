<?php

namespace Mush\Player\Event;

use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\RoomLog\Event\LoggableEventInterface;

class PlayerEvent extends PlayerCycleEvent implements LoggableEventInterface
{
    public const NEW_PLAYER = 'new.player';
    public const DEATH_PLAYER = 'death.player';
    public const METAL_PLATE = 'metal.plate';
    public const PANIC_CRISIS = 'panic.crisis';
    public const CYCLE_DISEASE = 'cycle_disease';
    public const INFECTION_PLAYER = 'infection.player';
    public const CONVERSION_PLAYER = 'conversion.player';
    public const END_PLAYER = 'end.player';
    public const DELETE_PLAYER = 'delete.player';
    public const CHANGED_PLACE = 'changed.place';
    public const TITLE_ATTRIBUTED = 'title.attributed';
    public const TITLE_REMOVED = 'title.removed';

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
