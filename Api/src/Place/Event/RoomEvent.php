<?php

namespace Mush\Place\Event;

use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Event\LoggableEventInterface;

class RoomEvent extends PlaceCycleEvent implements LoggableEventInterface
{
    public const TREMOR = 'tremor';
    public const ELECTRIC_ARC = 'electric.arc';
    public const DELETE_PLACE = 'delete.place';

    private string $visibility = VisibilityEnum::PUBLIC;

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function setVisibility(string $visibility): self
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function getLogParameters(): array
    {
        return [];
    }
}
