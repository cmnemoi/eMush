<?php

namespace Mush\Alert\Entity\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Alert\Entity\AlertElement;
use Mush\Equipment\Entity\Door;

class AlertElementCollection extends ArrayCollection
{
    public function getBrokenDoors(): AlertElementCollection
    {
        return $this->filter(fn (AlertElement $alert) => $alert->getEquipment() instanceof Door);
    }

    public function getBrokenEquipments(): AlertElementCollection
    {
        return $this->filter(fn (AlertElement $alert) => (($equipment = $alert->getEquipment()) !== null && !$equipment instanceof Door));
    }

    public function getFires(): AlertElementCollection
    {
        return $this->filter(fn (AlertElement $alert) => $alert->getEquipment() !== null);
    }

    public function getReportedAlert(): AlertElementCollection
    {
        return $this->filter(fn (AlertElement $alert) => $alert->getPlayer() !== null);
    }
}
