<?php

namespace Mush\Equipment\Entity\Mechanics;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\EquipmentMechanic;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Game\Entity\Collection\ProbaCollection;

#[ORM\Entity]
class PatrolShip extends EquipmentMechanic
{
    #[ORM\Column(type: 'array', nullable: true, options: ['default' => '[]'])]
    private array $collectScrapNumber = [];

    #[ORM\Column(type: 'array', nullable: true, options: ['default' => '[]'])]
    private array $collectScrapPatrolShipDamage = [];

    #[ORM\Column(type: 'array', nullable: true, options: ['default' => '[]'])]
    private array $collectScrapPlayerDamage = [];

    #[ORM\Column(type: 'string', nullable: false, options: ['default' => ''])]
    private string $dockingPlace = '';

    #[ORM\Column(type: 'array', nullable: false, options: ['default' => '[]'])]
    private array $failedManoeuvreDaedalusDamage = [];

    #[ORM\Column(type: 'array', nullable: false, options: ['default' => '[]'])]
    private array $failedManoeuvrePatrolShipDamage = [];

    #[ORM\Column(type: 'array', nullable: false, options: ['default' => '[]'])]
    private array $failedManoeuvrePlayerDamage = [];

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $numberOfExplorationSteps = 0;

    public function getMechanics(): array
    {
        $mechanics = parent::getMechanics();
        $mechanics[] = EquipmentMechanicEnum::PATROL_SHIP;

        return $mechanics;
    }

    public function getCollectScrapNumber(): ProbaCollection
    {
        return new ProbaCollection($this->collectScrapNumber);
    }

    public function setCollectScrapNumber(array|ProbaCollection $collectScrapNumber): static
    {
        if ($collectScrapNumber instanceof ProbaCollection) {
            $collectScrapNumber = $collectScrapNumber->toArray();
        }

        $this->collectScrapNumber = $collectScrapNumber;

        return $this;
    }

    public function getCollectScrapPatrolShipDamage(): ProbaCollection
    {
        return new ProbaCollection($this->collectScrapPatrolShipDamage);
    }

    public function setCollectScrapPatrolShipDamage(array|ProbaCollection $collectScrapPatrolShipDamage): static
    {
        if ($collectScrapPatrolShipDamage instanceof ProbaCollection) {
            $collectScrapPatrolShipDamage = $collectScrapPatrolShipDamage->toArray();
        }

        $this->collectScrapPatrolShipDamage = $collectScrapPatrolShipDamage;

        return $this;
    }

    public function getCollectScrapPlayerDamage(): ProbaCollection
    {
        return new ProbaCollection($this->collectScrapPlayerDamage);
    }

    public function setCollectScrapPlayerDamage(array|ProbaCollection $collectScrapPlayerDamage): static
    {
        if ($collectScrapPlayerDamage instanceof ProbaCollection) {
            $collectScrapPlayerDamage = $collectScrapPlayerDamage->toArray();
        }

        $this->collectScrapPlayerDamage = $collectScrapPlayerDamage;

        return $this;
    }

    public function getDockingPlace(): string
    {
        return $this->dockingPlace;
    }

    public function setDockingPlace(string $dockingPlace): static
    {
        $this->dockingPlace = $dockingPlace;

        return $this;
    }

    public function getFailedManoeuvreDaedalusDamage(): ProbaCollection
    {
        return new ProbaCollection($this->failedManoeuvreDaedalusDamage);
    }

    public function setFailedManoeuvreDaedalusDamage(array|ProbaCollection $failedManoeuvreDaedalusDamage): static
    {
        if ($failedManoeuvreDaedalusDamage instanceof ProbaCollection) {
            $failedManoeuvreDaedalusDamage = $failedManoeuvreDaedalusDamage->toArray();
        }

        $this->failedManoeuvreDaedalusDamage = $failedManoeuvreDaedalusDamage;

        return $this;
    }

    public function getFailedManoeuvrePatrolShipDamage(): ProbaCollection
    {
        return new ProbaCollection($this->failedManoeuvrePatrolShipDamage);
    }

    public function setFailedManoeuvrePatrolShipDamage(array|ProbaCollection $failedManoeuvrePatrolShipDamage): static
    {
        if ($failedManoeuvrePatrolShipDamage instanceof ProbaCollection) {
            $failedManoeuvrePatrolShipDamage = $failedManoeuvrePatrolShipDamage->toArray();
        }

        $this->failedManoeuvrePatrolShipDamage = $failedManoeuvrePatrolShipDamage;

        return $this;
    }

    public function getFailedManoeuvrePlayerDamage(): ProbaCollection
    {
        return new ProbaCollection($this->failedManoeuvrePlayerDamage);
    }

    public function setFailedManoeuvrePlayerDamage(array|ProbaCollection $failedManoeuvrePlayerDamage): static
    {
        if ($failedManoeuvrePlayerDamage instanceof ProbaCollection) {
            $failedManoeuvrePlayerDamage = $failedManoeuvrePlayerDamage->toArray();
        }

        $this->failedManoeuvrePlayerDamage = $failedManoeuvrePlayerDamage;

        return $this;
    }

    public function getNumberOfExplorationSteps(): int
    {
        return $this->numberOfExplorationSteps;
    }

    public function setNumberOfExplorationSteps(int $numberOfExplorationSteps): static
    {
        $this->numberOfExplorationSteps = $numberOfExplorationSteps;

        return $this;
    }
}
