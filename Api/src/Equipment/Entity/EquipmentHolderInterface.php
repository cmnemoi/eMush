<?php

namespace Mush\Equipment\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Mush\Place\Entity\Place;

interface EquipmentHolderInterface
{
    public function addEquipment(Equipment $gameEquipment): static;

    public function removeEquipment(Equipment $gameEquipment): static;

    public function getEquipments(): Collection;

    public function setEquipments(ArrayCollection $equipments): static;

    public function getPlace(): Place;

    public function hasEquipmentByName(string $name): bool;

    public function hasOperationalEquipmentByName(string $name): bool;
}
