<?php

namespace Mush\Equipment\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\Config\SpaceShipConfig;

#[ORM\Entity]
class SpaceShip extends GameEquipment
{
    #[ORM\Column(type: 'string', nullable: false, options: ['default' => ''])]
    private string $dockingPlace;

    #[ORM\Column(type: 'string', nullable: false, options: ['default' => ''])]
    private string $patrolShipName;

    public function getDockingPlace(): string
    {
        return $this->dockingPlace;
    }

    public function setDockingPlace(string $dockingPlace): self
    {
        $this->dockingPlace = $dockingPlace;

        return $this;
    }

    public function getPatrolShipName(): string
    {
        return $this->patrolShipName;
    }

    public function setPatrolShipName(string $patrolShipName): self
    {
        $this->patrolShipName = $patrolShipName;

        return $this;
    }

    public function getEquipment(): SpaceShipConfig
    {
        $equipment = $this->equipment;
        if (!$equipment instanceof SpaceShipConfig) {
            throw new \LogicException('SpaceShip config should be a SpaceShipConfig');
        }

        return $equipment;
    }

    public function getLogName(): string
    {
        return $this->getPatrolShipName() ?: $this->getName();
    }
}
