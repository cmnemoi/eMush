<?php

namespace Mush\Equipment\Entity\Config;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\EquipmentHolderInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\SpaceShip;
use Mush\Game\Entity\Collection\ProbaCollection;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;

#[ORM\Entity]
class SpaceShipConfig extends EquipmentConfig
{
    #[ORM\Column(type: 'array', nullable: true, options: ['default' => '[]'])]
    private array $collectScrapNumber = [];

    #[ORM\Column(type: 'array', nullable: true, options: ['default' => '[]'])]
    private array $collectScrapPatrolShipDamage = [];

    #[ORM\Column(type: 'array', nullable: true, options: ['default' => '[]'])]
    private array $collectScrapPlayerDamage = [];

    #[ORM\Column(type: 'array', nullable: false, options: ['default' => '[]'])]
    private array $failedManoeuvreDaedalusDamage = [];

    #[ORM\Column(type: 'array', nullable: false, options: ['default' => '[]'])]
    private array $failedManoeuvrePatrolShipDamage = [];

    #[ORM\Column(type: 'array', nullable: false, options: ['default' => '[]'])]
    private array $failedManoeuvrePlayerDamage = [];

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $numberOfExplorationSteps = 0;

    public function createGameEquipment(EquipmentHolderInterface $holder): SpaceShip
    {
        // Do not allow GameEquipment holders to be players
        $holder = $holder instanceof Player ? $holder->getPlace() : $holder;
        if (($holder instanceof Place) === false) {
            throw new \InvalidArgumentException('The holder of a GameEquipment must be a Place');
        }

        $gameEquipment = new SpaceShip($holder);
        $gameEquipment
            ->setPatrolShipName($this->getEquipmentName())
            ->setDockingPlace($holder->getName())
            ->setName($this->getEquipmentShortName())
            ->setEquipment($this);

        return $gameEquipment;
    }

    public function getCollectScrapNumber(): ProbaCollection
    {
        return new ProbaCollection($this->collectScrapNumber);
    }

    public function setCollectScrapNumber(array|ProbaCollection $collectScrapNumber): self
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

    public function setCollectScrapPatrolShipDamage(array|ProbaCollection $collectScrapPatrolShipDamage): self
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

    public function setCollectScrapPlayerDamage(array|ProbaCollection $collectScrapPlayerDamage): self
    {
        if ($collectScrapPlayerDamage instanceof ProbaCollection) {
            $collectScrapPlayerDamage = $collectScrapPlayerDamage->toArray();
        }

        $this->collectScrapPlayerDamage = $collectScrapPlayerDamage;

        return $this;
    }

    public function getFailedManoeuvreDaedalusDamage(): ProbaCollection
    {
        return new ProbaCollection($this->failedManoeuvreDaedalusDamage);
    }

    public function setFailedManoeuvreDaedalusDamage(array|ProbaCollection $failedManoeuvreDaedalusDamage): self
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

    public function setFailedManoeuvrePatrolShipDamage(array|ProbaCollection $failedManoeuvrePatrolShipDamage): self
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

    public function setFailedManoeuvrePlayerDamage(array|ProbaCollection $failedManoeuvrePlayerDamage): self
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

    public function setNumberOfExplorationSteps(int $numberOfExplorationSteps): self
    {
        $this->numberOfExplorationSteps = $numberOfExplorationSteps;

        return $this;
    }
}
