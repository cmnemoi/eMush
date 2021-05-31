<?php

namespace Mush\Alert\Service;

use Mush\Alert\Entity\Alert;
use Mush\Daedalus\Entity\Daedalus;

interface AlertServiceInterface
{
    public function persist(Alert $alert): Alert;

    public function delete(Alert $alert): void;

    public function findByNameAndDaedalus(string $name, Daedalus $daedalus): ?Alert;

    public function hullAlert(Daedalus $daedalus, int $change): void;

    public function oxygenAlert(Daedalus $daedalus, int $change): void;
}
