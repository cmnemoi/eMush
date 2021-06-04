<?php

namespace Mush\Alert\Service;

use Mush\Alert\Entity\Alert;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Place\Entity\Place;

interface AlertServiceInterface
{
    public function persist(Alert $alert): Alert;

    public function delete(Alert $alert): void;

    public function findByNameAndDaedalus(string $name, Daedalus $daedalus): ?Alert;

    public function hullAlert(Daedalus $daedalus, int $change): void;

    public function oxygenAlert(Daedalus $daedalus, int $change): void;

    public function gravityAlert(Daedalus $daedalus, bool $activate): void;

    public function handleEquipmentBreak(GameEquipment $equipment): void;

    public function handleEquipmentRepair(GameEquipment $equipment): void;

    public function handleFireStart(Place $place): void;

    public function handleFireStop(Place $place): void;
}
