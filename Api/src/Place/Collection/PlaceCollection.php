<?php

declare(strict_types=1);

namespace Mush\Place\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Place\Entity\Place;

/**
 * @extends ArrayCollection<string, Place>
 */
final class PlaceCollection extends ArrayCollection
{
    public function getAllWithoutStatus(string $status): self
    {
        return $this->filter(static fn (Place $place) => !$place->hasStatus($status));
    }

    public function getAllWithStatus(string $status): self
    {
        return $this->filter(static fn (Place $place) => $place->hasStatus($status));
    }

    public function getAllWithAlivePlayers(): self
    {
        return $this->filter(static fn (Place $place) => $place->getPlayers()->getPlayerAlive()->count() > 0);
    }
}
