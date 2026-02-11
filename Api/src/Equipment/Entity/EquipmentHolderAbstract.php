<?php

namespace Mush\Equipment\Entity;

use Doctrine\Common\Collections\Collection;

abstract class EquipmentHolderAbstract implements EquipmentHolderInterface
{
    public function getItems(): Collection
    {
        /** @var Collection<array-key, GameItem> $items */
        $items = $this
            ->getEquipments()
            ->filter(static fn (GameEquipment $equipment) => ($equipment->getClassName() === GameItem::class));

        return $items;
    }

    public function getEquipmentByName(string $name): ?GameEquipment
    {
        $equipment = $this->getEquipmentsByNames([$name]);

        return $equipment->isEmpty() ? null : $equipment->first();
    }

    public function hasEquipmentByName(string $name): bool
    {
        return $this->getEquipmentByName($name) !== null;
    }

    public function doesNotHaveEquipmentByName(string $name): bool
    {
        return !$this->hasEquipmentByName($name);
    }

    public function getOperationalEquipmentByName(string $name): ?GameEquipment
    {
        $equipment = $this->getEquipmentsByNames([$name])->filter(static fn (GameEquipment $gameEquipment) => $gameEquipment->isOperational());

        return $equipment->isEmpty() ? null : $equipment->first();
    }

    public function hasOperationalEquipmentByName(string $name): bool
    {
        return $this->getOperationalEquipmentByName($name) !== null;
    }

    public function doesNotHaveOperationalEquipmentByName(string $name): bool
    {
        return !$this->hasOperationalEquipmentByName($name);
    }

    public function getEquipmentsByNames(array $names): Collection
    {
        return $this->getEquipments()->filter(static fn (GameEquipment $gameEquipment) => \in_array($gameEquipment->getName(), $names, true));
    }

    public function hasAnyEquipmentsByNames(array $names): bool
    {
        return !$this->getEquipmentsByNames($names)->isEmpty();
    }

    public function getOperationalEquipmentsByNames(array $names): Collection
    {
        return $this->getEquipmentsByNames($names)->filter(static fn (GameEquipment $gameEquipment) => $gameEquipment->isOperational());
    }

    public function hasAnyOperationalEquipmentsByNames(array $names): bool
    {
        return !$this->getOperationalEquipmentsByNames($names)->isEmpty();
    }
}
