<?php

namespace Mush\Equipment\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Mush\Place\Entity\Place;

interface EquipmentHolderInterface
{
    public function addEquipment(GameEquipment $gameEquipment): self;

    public function removeEquipment(GameEquipment $gameEquipment): self;

    public function getEquipments(): Collection;

    public function setEquipments(ArrayCollection $equipments): self;

    public function getPlace(): Place;
}
