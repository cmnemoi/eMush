<?php

declare(strict_types=1);

namespace Mush\Place\Trait;

use Doctrine\Common\Collections\Collection;
use Mush\Place\Collection\PlaceCollection;
use Mush\Place\Entity\Place;
use Mush\Place\Entity\PlaceHolderInterface;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Place\Enum\RoomEnum;

/**
 * Trait for entities manipulating a collection of `Place`.
 *
 * The using entity should still implement the `getPlaces()` and
 * `setPlaceHolder()` methods.
 *
 * @mixin PlaceHolderInterface
 *
 * @property Collection $places
 */
trait PlaceHolderTrait
{
    public function setPlaces(PlaceCollection $places): static
    {
        $this->places = $places;

        foreach ($places as $place) {
            $this->setPlaceHolder($place);
        }

        return $this;
    }

    public function addPlace(Place $place): static
    {
        if (!$this->places->contains($place)) {
            $this->places->add($place);
            $this->setPlaceHolder($place);
        }

        return $this;
    }

    public function removePlace(Place $place): static
    {
        $this->places->removeElement($place);

        return $this;
    }

    public function getPlaceByName(string $name): ?Place
    {
        $place = $this->getPlaces()->filter(static fn (Place $place) => $place->getName() === $name)->first();

        return $place === false ? null : $place;
    }

    public function getPlaceByNameOrThrow(string $name): Place
    {
        $place = $this->getPlaceByName($name);
        if (!$place) {
            throw new \RuntimeException(static::class . " should have a place named {$name}");
        }

        return $place;
    }

    public function getRooms(): PlaceCollection
    {
        return $this->getPlaces()->filter(static fn (Place $place) => $place->getType() === PlaceTypeEnum::ROOM);
    }

    public function getStorages(): PlaceCollection
    {
        return $this->getPlaces()->filter(static fn (Place $place) => \in_array($place->getName(), RoomEnum::getStorages(), true));
    }

    public function getTabulatrixQueue(): Place
    {
        $queue = $this->getPlaces()
            ->filter(static fn (Place $place) => $place->getName() === RoomEnum::TABULATRIX_QUEUE)->first();
        if (!$queue) {
            throw new \RuntimeException(static::class . ' should have a place named TabulatrixQueue');
        }

        return $queue;
    }

    /**
     * @throws \RuntimeException if no planet place have been found
     */
    public function getPlanetPlace(): Place
    {
        $planetPlace = $this->getPlaces()->filter(static fn (Place $place) => $place->getName() === RoomEnum::PLANET)->first();
        if (!$planetPlace) {
            throw new \RuntimeException(static::class . ' should have a planet place');
        }

        return $planetPlace;
    }

    public function getSpace(): Place
    {
        $space = $this->getPlaces()->filter(static fn (Place $place) => $place->getName() === RoomEnum::SPACE)->first();
        if (!$space) {
            throw new \RuntimeException(static::class . ' should have a place named Space');
        }

        return $space;
    }
}
