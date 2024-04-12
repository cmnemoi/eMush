<?php

namespace Mush\Alert\Entity\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Alert\Entity\AlertElement;
use Mush\Equipment\Entity\Door;

/**
 * @template-extends ArrayCollection<int, AlertElement>
 */
class AlertElementCollection extends ArrayCollection
{
    public function getBrokenDoors(): self
    {
        return $this->filter(static fn (AlertElement $alert) => $alert->getEquipment() instanceof Door);
    }

    public function getBrokenEquipments(): self
    {
        return $this->filter(static fn (AlertElement $alert) => (($equipment = $alert->getEquipment()) !== null && !$equipment instanceof Door));
    }

    public function getFires(): self
    {
        return $this->filter(static fn (AlertElement $alert) => $alert->getEquipment() !== null);
    }

    public function getReportedAlert(): self
    {
        return $this->filter(static fn (AlertElement $alert) => $alert->getPlayerInfo() !== null);
    }
}
