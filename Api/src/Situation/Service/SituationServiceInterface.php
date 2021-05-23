<?php

namespace Mush\Situation\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Situation\Entity\Situation;

interface SituationServiceInterface
{
    public function persist(Situation $situation): Situation;

    public function delete(Situation $situation): void;

    public function findByNameAndDaedalus(string $name, Daedalus $daedalus): ?Situation;

    public function hullSituation(Daedalus $daedalus, int $change): void;

    public function oxygenSituation(Daedalus $daedalus, int $change): void;
}
