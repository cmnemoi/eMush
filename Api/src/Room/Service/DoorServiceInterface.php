<?php

namespace Mush\Room\Service;

use Mush\Room\Entity\Door;

interface DoorServiceInterface
{
    public function persist(Door $door): Door;

    public function findById(int $id): ?Door;
}
