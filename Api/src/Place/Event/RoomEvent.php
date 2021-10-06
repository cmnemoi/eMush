<?php

namespace Mush\Place\Event;

use Mush\Game\Event\AbstractLoggedEvent;
use Mush\RoomLog\Enum\VisibilityEnum;

class RoomEvent extends PlaceCycleEvent implements AbstractLoggedEvent
{
    public const TREMOR = 'tremor';
    public const ELECTRIC_ARC = 'electric.arc';
    public const STARTING_FIRE = 'starting.fire';
    public const STOP_FIRE = 'stop.fire';

    private bool $isGravity = true;
    private string $visibility = VisibilityEnum::PUBLIC;

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function setVisibility(string $visibility): RoomEvent
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function isGravity(): bool
    {
        return $this->isGravity;
    }

    public function setIsGravity(bool $isGravity): RoomEvent
    {
        $this->isGravity = $isGravity;

        return $this;
    }

    public function getLogParameters(): array
    {
        return [];
    }
}
