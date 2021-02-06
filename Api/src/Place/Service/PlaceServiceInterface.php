<?php

namespace Mush\Place\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Place\Entity\Place;
use Mush\Place\Entity\PlaceConfig;

interface PlaceServiceInterface
{
    public function persist(Place $room): Place;

    public function findById(int $id): ?Place;

    public function createPlace(PlaceConfig $roomConfig, Daedalus $daedalus): Place;
}
