<?php

namespace Mush\Disease\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Disease\Entity\Config\ConsumableDiseaseConfig;
use Mush\Disease\Enum\MedicalConditionTypeEnum;

#[ORM\Entity]
#[ORM\Table(name: 'disease_consummable_attribute')]
class ConsumableDiseaseAttribute
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private ?int $id;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $disease;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $type = MedicalConditionTypeEnum::DISEASE;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $rate = 100;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $delayMin = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $delayLength = 0;

    #[ORM\ManyToOne(targetEntity: ConsumableDisease::class, inversedBy: 'diseaseAttributes')]
    private ConsumableDisease $consumableDisease;

    #[ORM\ManyToOne(targetEntity: ConsumableDiseaseConfig::class, inversedBy: 'consumableAttributes')]
    private ConsumableDiseaseConfig $consumableDiseaseConfig;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDisease(): string
    {
        return $this->disease;
    }

    public function setDisease(string $disease): self
    {
        $this->disease = $disease;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getRate(): int
    {
        return $this->rate;
    }

    public function setRate(int $rate): self
    {
        $this->rate = $rate;

        return $this;
    }

    public function getDelayMin(): int
    {
        return $this->delayMin;
    }

    public function setDelayMin(int $delayMin): self
    {
        $this->delayMin = $delayMin;

        return $this;
    }

    public function getDelayLength(): int
    {
        return $this->delayLength;
    }

    public function setDelayLength(int $delayLength): self
    {
        $this->delayLength = $delayLength;

        return $this;
    }

    public function getConsumableDisease(): ConsumableDisease
    {
        return $this->consumableDisease;
    }

    public function setConsumableDisease(ConsumableDisease $consumableDisease): self
    {
        $this->consumableDisease = $consumableDisease;
        $consumableDisease->addDiseaseAttribute($this);

        return $this;
    }

    public function getConsumableDiseaseConfig(): ConsumableDiseaseConfig
    {
        return $this->consumableDiseaseConfig;
    }

    public function setConsumableDiseaseConfig(ConsumableDiseaseConfig $consumableDiseaseConfig): self
    {
        $this->consumableDiseaseConfig = $consumableDiseaseConfig;
        $consumableDiseaseConfig->addDisease($this);

        return $this;
    }
}
