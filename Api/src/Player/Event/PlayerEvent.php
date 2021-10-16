<?php

namespace Mush\Player\Event;

use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Event\LoggableEventInterface;

class PlayerEvent extends PlayerCycleEvent implements LoggableEventInterface
{
    public const NEW_PLAYER = 'new.player';
    public const DEATH_PLAYER = 'death.player';
    public const METAL_PLATE = 'metal.plate';
    public const PANIC_CRISIS = 'panic.crisis';
    public const INFECTION_PLAYER = 'infection.player';
    public const CONVERSION_PLAYER = 'conversion.player';
    public const END_PLAYER = 'end.player';

    protected string $visibility = VisibilityEnum::PRIVATE;

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function setVisibility(string $visibility): PlayerEvent
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function getPlace(): Place
    {
        return $this->player->getPlace();
    }

    public function getLogParameters(): array
    {
        return [
            $this->player->getLogKey() => $this->player->getLogName(),
        ];
    }
}
