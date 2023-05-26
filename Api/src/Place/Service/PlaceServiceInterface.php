<?php

namespace Mush\Place\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Place\Entity\Place;
use Mush\Place\Entity\PlaceConfig;

interface PlaceServiceInterface
{
    public function persist(Place $place): Place;

    public function delete(Place $place): bool;

    public function findById(int $id): ?Place;

    public function findByNameAndDaedalus(string $name, Daedalus $daedalus): ?Place;

    public function createPlace(PlaceConfig $roomConfig, Daedalus $daedalus, array $reasons, \DateTime $time): Place;
}
