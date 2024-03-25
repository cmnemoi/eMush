<?php

namespace Mush\Alert\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Alert\Entity\Alert;
use Mush\Alert\Entity\AlertElement;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Place\Entity\Place;

interface AlertServiceInterface
{
    public function persist(Alert $alert): Alert;

    public function delete(Alert $alert): void;

    public function persistAlertElement(AlertElement $alertElement): AlertElement;

    public function deleteAlertElement(AlertElement $alertElement): void;

    public function findByNameAndDaedalus(string $name, Daedalus $daedalus): ?Alert;

    public function findByDaedalus(Daedalus $daedalus): ArrayCollection;

    public function hullAlert(Daedalus $daedalus): void;

    public function oxygenAlert(Daedalus $daedalus): void;

    public function gravityAlert(Daedalus $daedalus, string $activate): void;

    public function handleEquipmentBreak(GameEquipment $equipment): void;

    public function handleEquipmentRepair(GameEquipment $equipment): void;

    public function getAlertEquipmentElement(Alert $alert, GameEquipment $equipment): AlertElement;

    public function handleFireStart(Place $place): void;

    public function handleFireStop(Place $place): void;

    public function getAlertFireElement(Alert $alert, Place $place): AlertElement;

    public function getAlerts(Daedalus $daedalus): ArrayCollection;

    public function handleHunterArrival(Daedalus $daedalus): void;

    public function handleHunterDeath(Daedalus $daedalus): void;

    public function handleSatietyAlert(Daedalus $daedalus): void;

    public function isFireReported(Place $room): bool;

    public function isEquipmentReported(GameEquipment $equipment): bool;

    public function handlePlayerLost(Daedalus $daedalus): void;

    public function handleLostPlayerFound(Daedalus $daedalus): void;
}
