<?php

namespace Mush\Disease\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Disease\Enum\TypeEnum;
use Mush\Equipment\Entity\ConsumableEffect;

/**
 * @ORM\Entity
 * @ORM\Table(name="disease_consummable_attribute")
 */
class ConsumableDiseaseAttribute
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
     * @ORM\Column(type="string", nullable=false)
     */
    private string $type = TypeEnum::DISEASE;

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
     * @ORM\ManyToOne (targetEntity="Mush\Equipment\Entity\ConsumableEffect", inversedBy="diseaseAttributes")
     */
    private ConsumableEffect $consumableEffect;

    /**
     * @ORM\ManyToOne (targetEntity="Mush\Disease\Entity\ConsumableDiseaseConfig", inversedBy="consumableAttributes")
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

    public function setDisease(string $disease): ConsumableDiseaseAttribute
    {
        $this->disease = $disease;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): ConsumableDiseaseAttribute
    {
        $this->type = $type;

        return $this;
    }

    public function getRate(): int
    {
        return $this->rate;
    }

    public function setRate(int $rate): ConsumableDiseaseAttribute
    {
        $this->rate = $rate;

        return $this;
    }

    public function getDelayMin(): int
    {
        return $this->delayMin;
    }

    public function setDelayMin(int $delayMin): ConsumableDiseaseAttribute
    {
        $this->delayMin = $delayMin;

        return $this;
    }

    public function getDelayLength(): int
    {
        return $this->delayLength;
    }

    public function setDelayLength(int $delayLength): ConsumableDiseaseAttribute
    {
        $this->delayLength = $delayLength;

        return $this;
    }

    public function getConsumableEffect(): ConsumableEffect
    {
        return $this->consumableEffect;
    }

    public function setConsumableEffect(ConsumableEffect $consumableDisease): ConsumableDiseaseAttribute
    {
        $this->consumableEffect = $consumableDisease;
        $consumableDisease->addDiseaseAttribute($this);

        return $this;
    }

    public function getConsumableDiseaseConfig(): ConsumableDiseaseConfig
    {
        return $this->consumableDiseaseConfig;
    }

    public function setConsumableDiseaseConfig(ConsumableDiseaseConfig $consumableDiseaseConfig): ConsumableDiseaseAttribute
    {
        $this->consumableDiseaseConfig = $consumableDiseaseConfig;
        $consumableDiseaseConfig->addDisease($this);

        return $this;
    }
}
