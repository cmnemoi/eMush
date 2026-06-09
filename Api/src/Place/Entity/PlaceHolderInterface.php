<?php

declare(strict_types=1);

namespace Mush\Place\Entity;

use Mush\Place\Collection\PlaceCollection;

/**
 * Interface for entities manipulating a collection of `Place`.
 */
interface PlaceHolderInterface
{
    public function getPlaces(): PlaceCollection;

    public function setPlaceHolder(Place $place): static;

    public function setPlaces(PlaceCollection $places): static;

    public function addPlace(Place $place): static;

    public function removePlace(Place $place): static;

    public function getPlaceByName(string $name): ?Place;

    public function getPlaceByNameOrThrow(string $name): Place;

    public function getRooms(): PlaceCollection;

    public function getStorages(): PlaceCollection;

    public function getTabulatrixQueue(): Place;

    public function getPlanetPlace(): Place;

    public function getSpace(): Place;
}
