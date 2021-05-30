<?php

namespace Mush\Alert\Entity\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Alert\Entity\ReportedAlert;
use Mush\Equipment\Entity\Door;

class ReportedAlertCollection extends ArrayCollection
{
    public function getBrokenDoors(): ReportedAlertCollection
    {
        return $this->filter(fn (ReportedAlert $alert) => $alert->getEquipment() instanceof Door);
    }

    public function getBrokenEquipments(): ReportedAlertCollection
    {
        return $this->filter(fn (ReportedAlert $alert) => (($equipment = $alert->getEquipment()) !== null && !$equipment instanceof Door));
    }

    public function getFires(): ReportedAlertCollection
    {
        return $this->filter(fn (ReportedAlert $alert) => $alert->getEquipment() !== null);
    }

    public function getReportedAlert(): ReportedAlertCollection
    {
        return $this->filter(fn (ReportedAlert $alert) => $alert->getPlayer() !== null);
    }
}
