<?php

namespace Mush\Disease\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="disease_consummable_characterisitc")
 */
class ConsumableDiseaseCharacteristic
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private ?int $id;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private string $disease;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $rate = 100;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $delayMin = 0;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $delayLength = 0;

    /**
     * @ORM\ManyToOne (targetEntity="Mush\Disease\Entity\Consumabledisease", inversedBy="diseases")
     */
    private ConsumableDisease $consumableDisease;

    /**
     * @ORM\ManyToOne (targetEntity="Mush\Disease\Entity\ConsumableDiseaseConfig", inversedBy="diseases")
     */
    private ConsumableDiseaseConfig $consumableDiseaseConfig;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDisease(): string
    {
        return $this->disease;
    }

    public function setDisease(string $disease): ConsumableDiseaseCharacteristic
    {
        $this->disease = $disease;

        return $this;
    }

    public function getRate(): int
    {
        return $this->rate;
    }

    public function setRate(int $rate): ConsumableDiseaseCharacteristic
    {
        $this->rate = $rate;

        return $this;
    }

    public function getDelayMin(): int
    {
        return $this->delayMin;
    }

    public function setDelayMin(int $delayMin): ConsumableDiseaseCharacteristic
    {
        $this->delayMin = $delayMin;

        return $this;
    }

    public function getDelayLength(): int
    {
        return $this->delayLength;
    }

    public function setDelayLength(int $delayLength): ConsumableDiseaseCharacteristic
    {
        $this->delayLength = $delayLength;

        return $this;
    }

    public function getConsumableDisease(): ConsumableDisease
    {
        return $this->consumableDisease;
    }

    public function setConsumableDisease(ConsumableDisease $consumableDisease): ConsumableDiseaseCharacteristic
    {
        $this->consumableDisease = $consumableDisease;
        $consumableDisease->addDisease($this);

        return $this;
    }

    public function getConsumableDiseaseConfig(): ConsumableDiseaseConfig
    {
        return $this->consumableDiseaseConfig;
    }

    public function setConsumableDiseaseConfig(ConsumableDiseaseConfig $consumableDiseaseConfig): ConsumableDiseaseCharacteristic
    {
        $this->consumableDiseaseConfig = $consumableDiseaseConfig;
        $consumableDiseaseConfig->addDisease($this);

        return $this;
    }
}
