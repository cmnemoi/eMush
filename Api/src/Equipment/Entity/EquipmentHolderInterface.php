<?php

namespace Mush\Equipment\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Place\Entity\Place;

interface EquipmentHolderInterface
{
    /**
     * Add equipment to the EquipmentHolder.
     *
     * @return $this
     */
    public function addEquipment(GameEquipment $gameEquipment): static;

    /**
     * Remove equipment from the EquipmentHolder.
     *
     * @return $this
     */
    public function removeEquipment(GameEquipment $gameEquipment): static;

    /**
     * Return all equipments of the EquipmentHolder.
     *
     * @return Collection<array-key, GameEquipment>
     */
    public function getEquipments(): Collection;

    /**
     * Return all items of the EquipmentHolder.
     *
     * @return Collection<array-key, GameItem>
     */
    public function getItems(): Collection;

    /**
     * Set equipments of the EquipmentHolder.
     *
     * @param ArrayCollection<array-key, GameEquipment> $equipments
     *
     * @return $this
     */
    public function setEquipments(ArrayCollection $equipments): static;

    /**
     * Return the Place where the EquipmentHolder is located.
     */
    public function getPlace(): Place;

    /**
     * Return the Daedalus instance of the EquipmentHolder.
     */
    public function getDaedalus(): Daedalus;

    /**
     * Return the first equipment of the EquipmentHolder with the given name.
     *
     * @param string $name name of the equipment to retrieve
     */
    public function getEquipmentByName(string $name): ?GameEquipment;

    /**
     * Check if the EquipmentHolder has equipment by name.
     *
     * @param string $name name of the equipment to check
     */
    public function hasEquipmentByName(string $name): bool;

    /**
     * Check if the EquipmentHolder does not have equipment by name.
     *
     * @param string $name name of the equipment to check
     */
    public function doesNotHaveEquipmentByName(string $name): bool;

    /**
     * Return the first operational equipment of the EquipmentHolder with the given name.
     *
     * @param string $name name of the equipment to retrieve
     */
    public function getOperationalEquipmentByName(string $name): ?GameEquipment;

    /**
     * Check if the EquipmentHolder has operational equipment by name.
     *
     * @param string $name name of the equipment to check
     */
    public function hasOperationalEquipmentByName(string $name): bool;

    /**
     * Check if the EquipmentHolder does not have operational equipment by name.
     *
     * @param string $name name of the equipment to check
     */
    public function doesNotHaveOperationalEquipmentByName(string $name): bool;

    /**
     * Return all equipments of the EquipmentHolder by their names.
     *
     * The resulting collection can have multiple items of the same name.
     *
     * @param array<string> $names array of equipment names to retrieve
     *
     * @return Collection<array-key, GameEquipment>
     */
    public function getEquipmentsByNames(array $names): Collection;

    /**
     * Check if the EquipmentHolder has any equipments by names.
     *
     * @param array<string> $names array of equipment names to check
     */
    public function hasAnyEquipmentsByNames(array $names): bool;

    /**
     * Return all operational equipments of the EquipmentHolder by their names.
     *
     * @param array<string> $names array of equipment names to retrieve
     *
     * @return Collection<array-key, GameEquipment>
     */
    public function getOperationalEquipmentsByNames(array $names): Collection;

    /**
     * Check if the EquipmentHolder has any operational equipments by names.
     *
     * The resulting collection can have multiple items of the same name.
     *
     * @param array<string> $names array of equipment names to check
     */
    public function hasAnyOperationalEquipmentsByNames(array $names): bool;
}
