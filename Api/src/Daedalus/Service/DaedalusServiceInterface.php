<?php

namespace Mush\Daedalus\Service;

use Mush\DAaedalus\Entity\Collection\DaedalusCollection;
use Mush\Daedalus\Criteria\DaedalusCriteria;
use Mush\Daedalus\Entity\Daedalus;

interface DaedalusServiceInterface
{
    public function persist(Daedalus $daedalus): Daedalus;

    public function findById(int $id): ?Daedalus;

    public function findByCriteria(DaedalusCriteria $criteria): DaedalusCollection;

    public function createDaedalus(): Daedalus;
}