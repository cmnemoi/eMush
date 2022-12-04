<?php

namespace Mush\Equipment\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Place\Entity\Place;

interface EquipmentHolderInterface
{
    public function addEquipment(GameEquipment $gameEquipment): static;

    public function removeEquipment(GameEquipment $gameEquipment): static;

    public function getEquipments(): Collection;

    public function setEquipments(ArrayCollection $equipments): static;

    public function getPlace(): Place;

    public function getDaedalus(): Daedalus;

    public function hasEquipmentByName(string $name): bool;

    public function hasOperationalEquipmentByName(string $name): bool;
}
